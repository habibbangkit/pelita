<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Dimension;
use App\Models\MaturityLevel;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\PrivacySetting;
use App\Models\Recommendation;
use App\Models\User;
use Database\Seeders\AssessmentFoundationSeeder;
use Database\Seeders\PelitaAssessmentContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendAssessmentResultEmail;
use App\Mail\AssessmentResultMail;
use App\Services\AssessmentReportService;
use Tests\TestCase;

class AssessmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_start_answer_navigate_and_finish_an_assessment(): void
    {
        Queue::fake();
        [$firstQuestion, $firstOption, $secondQuestion, $secondOption] = $this->createPublishedAssessment();

        $this->get('/')
            ->assertOk()
            ->assertSee('PELITA')
            ->assertSee('Pemetaan Literasi dan Kesiapan Teknologi Artificial Intelligence')
            ->assertSee('Kenali Kemampuanmu, Siap Hadapi Era Artificial Intelligence.')
            ->assertSee('Mengenal PELITA')
            ->assertSee('Apa yang Diukur PELITA?')
            ->assertSee('Bagaimana Cara Kerjanya?')
            ->assertSee('Tingkat Kematangan AI')
            ->assertSee('0–39')
            ->assertSee('40–74')
            ->assertSee('75–100')
            ->assertSee('Mengapa Mengikuti PELITA?')
            ->assertSee('Siap Mengukur Kesiapanmu Menghadapi Era AI?')
            ->assertSee('Mulai asesmen')
            ->assertSee('Mulai Asesmen Sekarang')
            ->assertSee('© 2026 PELITA. All Rights Reserved.')
            ->assertSee('href="'.route('home').'#mulai-asesmen"', false);

        $response = $this->post(route('assessment.start'), [
            'name' => '  Peserta Uji  ',
            'email' => '  TEST@example.com ',
        ]);

        $attempt = AssessmentAttempt::query()->firstOrFail();
        $this->assertSame('Peserta Uji', $attempt->participant->name);
        $this->assertSame('test@example.com', $attempt->participant->email);
        $response->assertRedirect(route('assessment.show', $attempt));
        $this->get(route('assessment.complete', $attempt))
            ->assertRedirect(route('assessment.show', $attempt));
        $this->get(route('assessment.download', $attempt))
            ->assertNotFound();

        $this->get(route('assessment.show', $attempt))
            ->assertOk()
            ->assertSee('Pertanyaan 1');

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $firstQuestion->id,
            'question_option_id' => $firstOption->id,
            'action' => 'next',
        ])->assertRedirect(route('assessment.show', ['attempt' => $attempt, 'question' => 1]));

        $this->assertDatabaseHas('answers', [
            'attempt_id' => $attempt->id,
            'question_id' => $firstQuestion->id,
            'question_option_id' => $firstOption->id,
        ]);

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $secondQuestion->id,
            'action' => 'previous',
        ])->assertRedirect(route('assessment.show', ['attempt' => $attempt, 'question' => 0]));

        $this->get(route('assessment.show', ['attempt' => $attempt, 'question' => 0]))
            ->assertOk()
            ->assertSee('checked', false);

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $firstQuestion->id,
            'action' => 'next',
        ])->assertRedirect(route('assessment.show', ['attempt' => $attempt, 'question' => 1]));

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $secondQuestion->id,
            'action' => 'finish',
        ])->assertRedirect(route('assessment.show', ['attempt' => $attempt, 'question' => 1]))
            ->assertSessionHasErrors('assessment');

        $this->assertSame('in_progress', $attempt->fresh()->status);

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $secondQuestion->id,
            'question_option_id' => $secondOption->id,
            'action' => 'finish',
        ])->assertRedirect(route('assessment.complete', $attempt));

        $this->assertSame('completed', $attempt->fresh()->status);
        $this->assertNotNull($attempt->fresh()->completed_at);
        $this->assertSame('81.25', $attempt->fresh()->total_score);
        $this->assertSame('Transformer', $attempt->fresh()->maturity_level);
        $this->assertDatabaseHas('attempt_dimension_scores', [
            'attempt_id' => $attempt->id,
            'dimension_id' => $firstQuestion->dimension_id,
            'score' => '81.25',
            'weight_snapshot' => '100.00',
        ]);
        $this->get(route('assessment.complete', $attempt))
            ->assertOk()
            ->assertSee('81.25')
            ->assertSee('Transformer')
            ->assertSee('Semua dimensi mencapai skor 60 atau lebih.')
            ->assertSee('Tinjau jawaban Anda')
            ->assertSee('Unduh laporan PDF')
            ->assertSee('akan dikirim ke test@example.com');

        Queue::assertPushed(
            SendAssessmentResultEmail::class,
            fn (SendAssessmentResultEmail $job) => $job->attemptId === $attempt->id
        );
        $this->assertSame('pending', $attempt->fresh()->result_email_status);
        Mail::fake();
        config(['mail.default' => 'smtp']);
        (new SendAssessmentResultEmail($attempt->id))->handle(app(AssessmentReportService::class));
        Mail::assertSent(
            AssessmentResultMail::class,
            fn (AssessmentResultMail $mail) => $mail->hasTo('test@example.com')
        );
        $this->assertSame('sent', $attempt->fresh()->result_email_status);
        $this->assertNotNull($attempt->fresh()->result_email_sent_at);

        $attempt->participant->update([
            'name' => ' =HYPERLINK("https://example.test","open")',
        ]);
        $download = $this->get(route('assessment.download', $attempt));
        $download->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $pdf = $download->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(1000, strlen($pdf));

        $snapshot = $attempt->fresh()->scoring_snapshot;
        $this->assertEquals(1, $snapshot['questions'][0]['weight']);
        $this->assertTrue($snapshot['questions'][0]['option_is_correct']);
        $this->assertEquals(3, $snapshot['questions'][1]['weight']);

        Dimension::query()->whereKey($firstQuestion->dimension_id)->update([
            'name' => 'Nama dimensi baru',
            'weight' => 90,
        ]);
        $this->assertSame('81.25', $attempt->fresh()->total_score);
        $this->assertSame(
            'Literasi Dasar',
            $attempt->fresh()->dimensionScores->first()->dimension_name_snapshot
        );
    }

    public function test_start_requires_valid_identity_and_published_assessment(): void
    {
        $this->post(route('assessment.start'), [
            'name' => '',
            'email' => 'not-an-email',
        ])->assertSessionHasErrors(['name', 'email']);

        $this->post(route('assessment.start'), [
            'name' => 'Peserta',
            'email' => 'peserta@example.com',
        ])->assertSessionHasErrors('assessment');

        $this->assertDatabaseCount('assessment_attempts', 0);
    }

    public function test_answer_option_must_belong_to_the_current_question(): void
    {
        [$firstQuestion, , $secondQuestion, $secondOption] = $this->createPublishedAssessment();
        $attempt = $this->startAttempt();

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $firstQuestion->id,
            'question_option_id' => $secondOption->id,
            'action' => 'next',
        ])->assertSessionHasErrors('question_option_id');

        $this->assertDatabaseCount('answers', 0);
        $this->assertNotSame($firstQuestion->id, $secondQuestion->id);
    }

    public function test_wrong_multiple_choice_answer_scores_zero(): void
    {
        [$firstQuestion, , $secondQuestion, $secondOption] = $this->createPublishedAssessment();
        $wrongOption = QuestionOption::create([
            'question_id' => $firstQuestion->id,
            'option_text' => 'Pilihan yang salah',
            'is_correct' => false,
            'position' => 2,
        ]);
        $attempt = $this->startAttempt();

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $firstQuestion->id,
            'question_option_id' => $wrongOption->id,
            'action' => 'next',
        ]);
        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $secondQuestion->id,
            'question_option_id' => $secondOption->id,
            'action' => 'finish',
        ]);

        $this->assertSame('56.25', $attempt->fresh()->total_score);
        $this->assertSame('Adopter', $attempt->fresh()->maturity_level);
        $this->assertDatabaseHas('answers', [
            'attempt_id' => $attempt->id,
            'question_id' => $firstQuestion->id,
            'score' => '0.00',
        ]);
    }

    public function test_final_score_uses_dimension_weights_and_recommends_the_lowest_dimension(): void
    {
        $this->seedMaturityLevels();
        $literacy = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 25,
            'is_active' => true,
        ]);
        $ethics = Dimension::create([
            'name' => 'Etika & Keamanan',
            'slug' => 'etika-keamanan',
            'weight' => 75,
            'is_active' => true,
        ]);
        $assessment = Assessment::create([
            'title' => 'Asesmen Dua Dimensi',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $literacyQuestion = $this->createQuestion($assessment, $literacy, 1, 'Pertanyaan literasi?', true);
        $ethicsQuestion = $this->createQuestion($assessment, $ethics, 2, 'Pertanyaan etika?', false);
        $attempt = $this->startAttempt();

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $literacyQuestion['question']->id,
            'question_option_id' => $literacyQuestion['option']->id,
            'action' => 'next',
        ]);
        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $ethicsQuestion['question']->id,
            'question_option_id' => $ethicsQuestion['option']->id,
            'action' => 'finish',
        ])->assertRedirect(route('assessment.complete', $attempt));

        $this->assertSame('25.00', $attempt->fresh()->total_score);
        $this->assertSame('Aware', $attempt->fresh()->maturity_level);
        $this->assertSame('Etika & Keamanan', $attempt->fresh()->recommendation_snapshot[0]['dimension_name']);
    }

    public function test_results_include_recommendations_for_every_dimension_below_60(): void
    {
        $this->seedMaturityLevels();
        $literacy = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 50,
            'is_active' => true,
        ]);
        $ethics = Dimension::create([
            'name' => 'Etika & Keamanan',
            'slug' => 'etika-keamanan',
            'weight' => 50,
            'is_active' => true,
        ]);
        $assessment = Assessment::create([
            'title' => 'Asesmen Dua Dimensi',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $literacyQuestion = $this->createQuestion($assessment, $literacy, 1, 'Pertanyaan literasi?', true);
        $literacyQuestion['question']->update(['explanation' => 'Pahami konsep dasar AI.']);
        $ethicsQuestion = $this->createQuestion($assessment, $ethics, 2, 'Pertanyaan etika?', true);
        foreach ([
            [$literacyQuestion['question'], 'Pilihan literasi yang salah'],
            [$ethicsQuestion['question'], 'Pilihan etika yang salah'],
        ] as [$question, $label]) {
            $wrongOptions[] = QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $label,
                'is_correct' => false,
                'position' => 2,
            ]);
        }
        Recommendation::create([
            'dimension_id' => $literacy->id,
            'minimum_score' => 0,
            'maximum_score' => 59.99,
            'title' => 'Perkuat literasi dasar AI',
            'description' => 'Pelajari konsep dasar AI.',
            'is_active' => true,
        ]);
        Recommendation::create([
            'dimension_id' => $ethics->id,
            'minimum_score' => 0,
            'maximum_score' => 59.99,
            'title' => 'Tingkatkan etika AI',
            'description' => 'Pelajari keamanan dan privasi.',
            'is_active' => true,
        ]);
        $attempt = $this->startAttempt();

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $literacyQuestion['question']->id,
            'question_option_id' => $wrongOptions[0]->id,
            'action' => 'next',
        ]);
        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $ethicsQuestion['question']->id,
            'question_option_id' => $wrongOptions[1]->id,
            'action' => 'finish',
        ])->assertRedirect(route('assessment.complete', $attempt));

        $recommendations = $attempt->fresh()->recommendation_snapshot;
        $this->assertCount(2, $recommendations);
        $this->assertSame(
            ['Perkuat literasi dasar AI', 'Tingkatkan etika AI'],
            array_column($recommendations, 'title')
        );
        $this->get(route('assessment.complete', $attempt))
            ->assertOk()
            ->assertSee('Perkuat literasi dasar AI')
            ->assertSee('Tingkatkan etika AI')
            ->assertSee('Jawaban yang benar:')
            ->assertSee('Jawaban Literasi Dasar')
            ->assertSee('Pahami konsep dasar AI.');
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.participants.show', $attempt))
            ->assertOk()
            ->assertSee('Ringkasan hasil')
            ->assertSee('Perkuat literasi dasar AI')
            ->assertSee('Tingkatkan etika AI');
    }

    public function test_log_mailer_does_not_mark_assessment_email_as_sent(): void
    {
        Queue::fake();
        [$firstQuestion, $firstOption, $secondQuestion, $secondOption] = $this->createPublishedAssessment();
        $attempt = $this->startAttempt();

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $firstQuestion->id,
            'question_option_id' => $firstOption->id,
            'action' => 'next',
        ]);
        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $secondQuestion->id,
            'question_option_id' => $secondOption->id,
            'action' => 'finish',
        ]);

        Mail::fake();
        config(['mail.default' => 'log']);
        (new SendAssessmentResultEmail($attempt->id))->handle(app(AssessmentReportService::class));

        $this->assertSame('not_configured', $attempt->fresh()->result_email_status);
        Mail::assertNothingSent();
    }

    public function test_repeated_assessments_share_participant_identity_and_admin_can_view_history(): void
    {
        [$firstQuestion, $firstOption, $secondQuestion, $secondOption] = $this->createPublishedAssessment();
        $firstAttempt = $this->startAttempt();

        $this->post(route('assessment.answer', $firstAttempt), [
            'question_id' => $firstQuestion->id,
            'question_option_id' => $firstOption->id,
            'action' => 'next',
        ]);
        $this->post(route('assessment.answer', $firstAttempt), [
            'question_id' => $secondQuestion->id,
            'question_option_id' => $secondOption->id,
            'action' => 'finish',
        ])->assertRedirect(route('assessment.complete', $firstAttempt));

        $this->post(route('assessment.start'), [
            'name' => 'Nama Peserta Diperbarui',
            'email' => 'PESERTA@example.com',
        ])->assertRedirect();
        $secondAttempt = AssessmentAttempt::query()->whereKeyNot($firstAttempt->id)->firstOrFail();

        $this->assertDatabaseCount('participants', 1);
        $this->assertSame($firstAttempt->participant_id, $secondAttempt->participant_id);
        $this->assertSame('Nama Peserta Diperbarui', $secondAttempt->participant->name);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.participants.show', $firstAttempt))
            ->assertOk()
            ->assertSee('Riwayat pengerjaan peserta')
            ->assertSee('Asesmen Uji');
    }

    public function test_content_seeder_creates_the_twenty_question_instrument_and_four_recommendations(): void
    {
        $this->seed(PelitaAssessmentContentSeeder::class);
        $assessment = Assessment::query()->where('title', 'PELITA - AI Literacy & Readiness Assessment')->firstOrFail();
        $questions = $assessment->questions()->with('options')->orderBy('position')->get();
        $dimensionWeights = Dimension::query()->orderBy('slug')->pluck('weight', 'slug')->map(fn ($weight) => (float) $weight)->all();

        $this->assertSame('draft', $assessment->status);
        $this->assertSame(15, $assessment->duration_minutes);
        $this->assertSame([
            'etika-keamanan' => 20.0,
            'kesiapan-infrastruktur' => 20.0,
            'keterampilan-praktis' => 35.0,
            'literasi-dasar' => 25.0,
        ], $dimensionWeights);
        $this->assertCount(20, $questions);
        $this->assertSame(15, $questions->where('type', 'multiple_choice')->count());
        $this->assertSame(5, $questions->where('type', 'likert')->count());
        $this->assertSame([5, 5, 5, 5], $questions->groupBy('dimension_id')->map->count()->sort()->values()->all());
        $this->assertSame([4], $questions->where('type', 'multiple_choice')->map(fn ($question) => $question->options->count())->unique()->values()->all());
        $this->assertSame([5], $questions->where('type', 'likert')->map(fn ($question) => $question->options->count())->unique()->values()->all());
        foreach ($questions->where('type', 'multiple_choice') as $question) {
            $this->assertSame(1, $question->options->where('is_correct', true)->count());
        }
        foreach ($questions->where('type', 'likert') as $question) {
            $this->assertSame([0, 25, 50, 75, 100], $question->options->pluck('score_value')->sort()->values()->all());
        }
        $this->assertSame(4, Recommendation::query()->count());

        $questions->first()->update(['question_text' => 'Teks soal khusus admin?']);
        $this->seed(PelitaAssessmentContentSeeder::class);
        $this->assertSame('Teks soal khusus admin?', $questions->first()->fresh()->question_text);
        $this->assertDatabaseCount('questions', 20);
        $this->assertDatabaseCount('recommendations', 4);
    }

    public function test_invalid_dimension_weights_prevent_completion_without_saving_a_partial_result(): void
    {
        [$firstQuestion, $firstOption, $secondQuestion, $secondOption] = $this->createPublishedAssessment();
        Dimension::query()->whereKey($firstQuestion->dimension_id)->update(['weight' => 95]);
        $attempt = $this->startAttempt();

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $firstQuestion->id,
            'question_option_id' => $firstOption->id,
            'action' => 'next',
        ]);
        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $secondQuestion->id,
            'question_option_id' => $secondOption->id,
            'action' => 'finish',
        ])->assertRedirect(route('assessment.show', ['attempt' => $attempt, 'question' => 1]))
            ->assertSessionHasErrors('assessment');

        $this->assertSame('in_progress', $attempt->fresh()->status);
        $this->assertNull($attempt->fresh()->total_score);
        $this->assertDatabaseCount('attempt_dimension_scores', 0);
    }

    public function test_admin_authentication_and_route_protection(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

        $admin = User::forceCreate([
            'name' => 'PELITA Admin',
            'email' => 'admin@example.com',
            'password' => 'strong-test-password',
            'is_admin' => true,
        ]);

        $this->assertTrue(Hash::check('strong-test-password', $admin->password));

        $this->post(route('admin.login.submit'), [
            'email' => 'admin@example.com',
            'password' => 'strong-test-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.login',
        ]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::forceCreate([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'strong-test-password',
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_filter_inspect_and_export_completed_results(): void
    {
        [$firstQuestion, $firstOption, $secondQuestion, $secondOption] = $this->createPublishedAssessment();
        $attempt = $this->startAttempt();

        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $firstQuestion->id,
            'question_option_id' => $firstOption->id,
            'action' => 'next',
        ]);
        $this->post(route('assessment.answer', $attempt), [
            'question_id' => $secondQuestion->id,
            'question_option_id' => $secondOption->id,
            'action' => 'finish',
        ]);

        $admin = User::forceCreate([
            'name' => 'PELITA Admin',
            'email' => 'admin@example.com',
            'password' => 'strong-test-password',
            'is_admin' => true,
        ]);
        $this->actingAs($admin);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('1')
            ->assertSee('Transformer');

        $this->get(route('admin.participants.index', ['search' => 'peserta@example.com']))
            ->assertOk()
            ->assertSee('Peserta Uji')
            ->assertSee('81.25');

        $this->get(route('admin.participants.show', $attempt))
            ->assertOk()
            ->assertSee('Pertanyaan pertama?')
            ->assertSee('Pilihan kedua')
            ->assertSee('81.25')
            ->assertSee('Ringkasan hasil')
            ->assertSee('Kesimpulan')
            ->assertSee('Status pengiriman hasil');

        $attempt->participant->update(['name' => ' =SUM(1,1)']);
        $response = $this->get(route('admin.participants.export', ['level' => 'Transformer']));
        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Skor Akhir', $csv);
        $this->assertStringContainsString('peserta@example.com', $csv);
        $this->assertStringContainsString("' =SUM", $csv);
    }

    public function test_admin_can_manage_questions_and_publish_a_valid_assessment(): void
    {
        $dimension = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 100,
            'is_active' => true,
        ]);
        $assessment = Assessment::create([
            'title' => 'Asesmen Draf',
            'status' => 'draft',
        ]);
        $this->actingAs($this->makeAdmin());
        $this->configurePrivacySettings();

        $this->get(route('admin.questions.index'))
            ->assertOk()
            ->assertSee('Pertanyaan asesmen');

        $this->post(route('admin.questions.store'), [
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => 'Pertanyaan yang disetujui?',
            'type' => 'multiple_choice',
            'weight' => 1,
            'position' => 1,
            'is_required' => 1,
            'is_active' => 1,
            'correct_option' => 0,
            'options' => [
                ['option_text' => 'Benar'],
                ['option_text' => 'Salah'],
            ],
        ])->assertRedirect(route('admin.questions.index', ['assessment_id' => $assessment->id]));

        $question = Question::query()->firstOrFail();
        $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        $this->assertSame(2, $question->options()->where('is_active', true)->count());

        $this->post(route('admin.assessments.publish', $assessment))
            ->assertRedirect(route('admin.questions.index', ['assessment_id' => $assessment->id]));
        $this->assertSame('published', $assessment->fresh()->status);
        $this->get('/')->assertSee('Mulai asesmen');

        $this->patch(route('admin.questions.archive', $question))
            ->assertSessionHasErrors('assessment');
        $this->patch(route('admin.assessments.unpublish', $assessment))
            ->assertRedirect(route('admin.questions.index', ['assessment_id' => $assessment->id]));
        $this->patch(route('admin.questions.archive', $question))
            ->assertRedirect(route('admin.questions.index', ['assessment_id' => $assessment->id]));
        $this->assertFalse($question->fresh()->is_active);

        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => auth()->id(),
            'action' => 'question.created',
            'subject_type' => 'Question',
            'subject_id' => $question->id,
        ]);
        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => auth()->id(),
            'action' => 'assessment.published',
            'subject_type' => 'Assessment',
            'subject_id' => $assessment->id,
        ]);
        $this->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertSee('Aktivitas administrator')
            ->assertSee('Menambahkan pertanyaan')
            ->assertSee('Mempublikasikan asesmen');
    }

    public function test_admin_can_edit_question_options_and_reorder_questions(): void
    {
        $dimension = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 100,
            'is_active' => true,
        ]);
        $assessment = Assessment::create(['title' => 'Asesmen Draf', 'status' => 'draft']);
        $this->actingAs($this->makeAdmin());

        foreach ([
            ['text' => 'Pertanyaan satu?', 'position' => 1],
            ['text' => 'Pertanyaan dua?', 'position' => 2],
        ] as $questionData) {
            $this->post(route('admin.questions.store'), [
                'assessment_id' => $assessment->id,
                'dimension_id' => $dimension->id,
                'question_text' => $questionData['text'],
                'type' => 'multiple_choice',
                'weight' => 1,
                'position' => $questionData['position'],
                'is_required' => 1,
                'is_active' => 1,
                'correct_option' => 0,
                'options' => [
                    ['option_text' => 'Opsi benar'],
                    ['option_text' => 'Opsi salah'],
                ],
            ])->assertRedirect();
        }

        $firstQuestion = Question::query()->where('position', 1)->firstOrFail();
        $options = $firstQuestion->options()->orderBy('position')->get();

        $this->get(route('admin.questions.edit', $firstQuestion))
            ->assertOk()
            ->assertSee('Ubah pertanyaan');

        $this->put(route('admin.questions.update', $firstQuestion), [
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => 'Pertanyaan skala?',
            'type' => 'likert',
            'weight' => '1.25',
            'position' => 1,
            'is_required' => 1,
            'is_active' => 1,
            'options' => [
                ['id' => $options[0]->id, 'option_text' => 'Jarang', 'score_value' => 25],
                ['id' => $options[1]->id, 'option_text' => 'Selalu', 'score_value' => 100],
            ],
        ])->assertRedirect(route('admin.questions.index', ['assessment_id' => $assessment->id]));

        $this->assertSame('likert', $firstQuestion->fresh()->type);
        $this->assertSame('Pertanyaan skala?', $firstQuestion->fresh()->question_text);
        $this->assertSame(25, $options[0]->fresh()->score_value);
        $this->assertNull($options[0]->fresh()->is_correct);

        $this->put(route('admin.questions.update', $firstQuestion), [
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => 'Pertanyaan skala?',
            'type' => 'likert',
            'weight' => '1.25',
            'position' => 1,
            'is_required' => 1,
            'is_active' => 1,
            'options' => [
                ['id' => $options[0]->id, 'option_text' => 'Jarang', 'score_value' => 74],
                ['id' => $options[1]->id, 'option_text' => 'Selalu', 'score_value' => 100],
            ],
        ])->assertSessionHasErrors('options.0.score_value');

        $this->assertSame(25, $options[0]->fresh()->score_value);

        $this->patch(route('admin.questions.move', $firstQuestion), ['direction' => 'down'])
            ->assertRedirect(route('admin.questions.index', ['assessment_id' => $assessment->id]));

        $this->assertSame(2, $firstQuestion->fresh()->position);
        $this->assertSame('Pertanyaan dua?', Question::query()->where('position', 1)->value('question_text'));
    }

    public function test_publishing_rejects_a_question_without_exactly_one_correct_answer(): void
    {
        $this->configurePrivacySettings();
        $dimension = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 100,
            'is_active' => true,
        ]);
        $assessment = Assessment::create(['title' => 'Asesmen Draf', 'status' => 'draft']);
        $question = Question::create([
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => 'Pilih jawaban?',
            'type' => 'multiple_choice',
            'weight' => 1,
            'position' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);
        foreach (['Opsi satu', 'Opsi dua'] as $position => $label) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $label,
                'position' => $position + 1,
                'is_correct' => false,
            ]);
        }

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.assessments.publish', $assessment))
            ->assertSessionHasErrors('assessment');

        $this->assertSame('draft', $assessment->fresh()->status);
    }

    public function test_publishing_rejects_a_likert_score_outside_the_discrete_rubric(): void
    {
        $this->configurePrivacySettings();
        $dimension = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 100,
            'is_active' => true,
        ]);
        $assessment = Assessment::create(['title' => 'Asesmen Draf', 'status' => 'draft']);
        $question = Question::create([
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => 'Seberapa siap?',
            'type' => 'likert',
            'weight' => 1,
            'position' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);
        foreach ([74, 100] as $position => $score) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => 'Opsi '.$position,
                'score_value' => $score,
                'position' => $position + 1,
            ]);
        }

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.assessments.publish', $assessment))
            ->assertSessionHasErrors('assessment');

        $this->assertSame('draft', $assessment->fresh()->status);
    }

    public function test_scoring_settings_require_valid_dimension_weights_and_contiguous_level_ranges(): void
    {
        $this->seedMaturityLevels();
        $firstDimension = Dimension::create([
            'name' => 'Dimensi Satu',
            'slug' => 'dimensi-satu',
            'weight' => 40,
            'is_active' => true,
        ]);
        $secondDimension = Dimension::create([
            'name' => 'Dimensi Dua',
            'slug' => 'dimensi-dua',
            'weight' => 60,
            'is_active' => true,
        ]);
        $this->actingAs($this->makeAdmin());

        $payload = [
            'dimensions' => [
                $firstDimension->id => ['weight' => 30, 'is_active' => 1],
                $secondDimension->id => ['weight' => 70, 'is_active' => 1],
            ],
            'levels' => [
                1 => ['minimum_score' => 0, 'maximum_score' => 29.99],
                2 => ['minimum_score' => 30, 'maximum_score' => 69.99],
                3 => ['minimum_score' => 70, 'maximum_score' => 100],
            ],
        ];

        $payload['levels'][1]['maximum_score'] = 29.999;
        $this->from(route('admin.scoring-settings.index'))
            ->put(route('admin.scoring-settings.update'), $payload)
            ->assertSessionHasErrors('levels.1.maximum_score');
        $payload['levels'][1]['maximum_score'] = 29.99;

        $this->get(route('admin.scoring-settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan penilaian');
        $this->put(route('admin.scoring-settings.update'), $payload)
            ->assertRedirect(route('admin.scoring-settings.index'));

        $this->assertSame('30.00', $firstDimension->fresh()->weight);
        $this->assertSame('70.00', $secondDimension->fresh()->weight);
        $this->assertSame('29.99', MaturityLevel::query()->where('name', 'Aware')->value('maximum_score'));

        $payload['dimensions'][$firstDimension->id]['weight'] = 35;
        $this->from(route('admin.scoring-settings.index'))
            ->put(route('admin.scoring-settings.update'), $payload)
            ->assertSessionHasErrors('dimensions');
        $this->assertSame('30.00', $firstDimension->fresh()->weight);
    }

    public function test_question_weight_validation_matches_database_precision(): void
    {
        $dimension = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 100,
            'is_active' => true,
        ]);
        $assessment = Assessment::create(['title' => 'Asesmen Draf', 'status' => 'draft']);
        $this->actingAs($this->makeAdmin());

        foreach (['10000', '1.001'] as $weight) {
            $this->post(route('admin.questions.store'), [
                'assessment_id' => $assessment->id,
                'dimension_id' => $dimension->id,
                'question_text' => 'Pertanyaan?',
                'type' => 'multiple_choice',
                'weight' => $weight,
                'position' => 1,
                'is_required' => 1,
                'is_active' => 1,
                'correct_option' => 0,
                'options' => [
                    ['option_text' => 'Benar'],
                    ['option_text' => 'Salah'],
                ],
            ])->assertSessionHasErrors('weight');
        }

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_foundation_seeder_preserves_admin_scoring_changes(): void
    {
        $seeder = new AssessmentFoundationSeeder;
        $seeder->run();
        $dimension = Dimension::query()->where('slug', 'literasi-dasar')->firstOrFail();
        $level = MaturityLevel::query()->where('name', 'Aware')->firstOrFail();
        $dimension->update(['weight' => 30]);
        $level->update(['maximum_score' => 29.99]);

        $seeder->run();

        $this->assertSame('30.00', $dimension->fresh()->weight);
        $this->assertSame('29.99', $level->fresh()->maximum_score);
    }

    public function test_assessment_content_cannot_change_while_a_participant_is_in_progress(): void
    {
        [$firstQuestion] = $this->createPublishedAssessment();
        $attempt = $this->startAttempt();
        $this->actingAs($this->makeAdmin());

        $this->patch(route('admin.questions.archive', $firstQuestion))
            ->assertSessionHasErrors('assessment');
        $this->assertTrue($firstQuestion->fresh()->is_active);
        $this->assertSame('in_progress', $attempt->fresh()->status);
    }

    public function test_security_headers_are_applied_and_sensitive_pages_are_not_cached(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');

        [$firstQuestion] = $this->createPublishedAssessment();
        $attempt = $this->startAttempt();
        $this->get(route('assessment.show', $attempt))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    private function createPublishedAssessment(): array
    {
        $this->configurePrivacySettings();
        $this->seedMaturityLevels();
        $dimension = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 100,
            'is_active' => true,
        ]);
        $assessment = Assessment::create([
            'title' => 'Asesmen Uji',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $firstQuestion = Question::create([
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => 'Pertanyaan pertama?',
            'type' => 'multiple_choice',
            'weight' => 1,
            'position' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);
        $firstOption = QuestionOption::create([
            'question_id' => $firstQuestion->id,
            'option_text' => 'Pilihan pertama',
            'is_correct' => true,
            'position' => 1,
        ]);

        $secondQuestion = Question::create([
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => 'Pertanyaan kedua?',
            'type' => 'likert',
            'weight' => 3,
            'position' => 2,
            'is_required' => true,
            'is_active' => true,
        ]);
        $secondOption = QuestionOption::create([
            'question_id' => $secondQuestion->id,
            'option_text' => 'Pilihan kedua',
            'score_value' => 75,
            'position' => 1,
        ]);

        return [$firstQuestion, $firstOption, $secondQuestion, $secondOption];
    }

    private function configurePrivacySettings(): PrivacySetting
    {
        return PrivacySetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'retention_days' => 365,
                'contact_name' => 'Admin PELITA',
                'contact_email' => 'admin@example.com',
            ]
        );
    }

    private function createQuestion(Assessment $assessment, Dimension $dimension, int $position, string $text, bool $isCorrect): array
    {
        $question = Question::create([
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => $text,
            'type' => 'multiple_choice',
            'weight' => 1,
            'position' => $position,
            'is_required' => true,
            'is_active' => true,
        ]);
        $option = QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Jawaban '.$dimension->name,
            'is_correct' => $isCorrect,
            'position' => 1,
        ]);

        return ['question' => $question, 'option' => $option];
    }

    private function startAttempt(): AssessmentAttempt
    {
        $this->configurePrivacySettings();
        $this->post(route('assessment.start'), [
            'name' => 'Peserta Uji',
            'email' => 'peserta@example.com',
        ]);

        return AssessmentAttempt::query()->firstOrFail();
    }

    private function seedMaturityLevels(): void
    {
        if (MaturityLevel::query()->exists()) {
            return;
        }

        foreach (config('pelita.maturity_levels') as $position => $level) {
            MaturityLevel::create([
                'name' => $level['name'],
                'minimum_score' => $level['min_score'],
                'maximum_score' => $level['max_score'],
                'position' => $position + 1,
                'is_active' => true,
            ]);
        }
    }

    private function makeAdmin(): User
    {
        return User::forceCreate([
            'name' => 'PELITA Admin',
            'email' => 'admin@example.com',
            'password' => 'strong-test-password',
            'is_admin' => true,
        ]);
    }
}
