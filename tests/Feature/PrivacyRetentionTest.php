<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Dimension;
use App\Models\Participant;
use App\Models\PrivacySetting;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_settings_are_required_before_publication_and_participant_start(): void
    {
        $dimension = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 100,
            'is_active' => true,
        ]);
        $assessment = Assessment::create(['title' => 'Asesmen PELITA', 'status' => 'draft']);
        $question = Question::create([
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => 'Pertanyaan?',
            'type' => 'multiple_choice',
            'weight' => 1,
            'position' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);
        foreach (['Benar', 'Salah'] as $position => $label) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $label,
                'is_correct' => $position === 0,
                'position' => $position + 1,
            ]);
        }

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->post(route('admin.assessments.publish', $assessment))
            ->assertSessionHasErrors('privacy_settings');

        $assessment->update(['status' => 'published', 'published_at' => now()]);
        $this->post(route('assessment.start'), [
            'name' => 'Peserta Uji',
            'email' => 'peserta@example.com',
        ])->assertSessionHasErrors('assessment');

        $this->assertDatabaseCount('participants', 0);
        $this->get('/')
            ->assertOk()
            ->assertSee('Asesmen belum dapat dimulai')
            ->assertDontSee('action="'.route('assessment.start').'"', false);
    }

    public function test_admin_can_save_privacy_contact_and_retention_policy(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->put(route('admin.privacy-settings.update'), [
                'retention_days' => 365,
                'contact_name' => 'admin',
                'contact_email' => 'ADMIN@gmail.com',
            ])
            ->assertRedirect(route('admin.privacy-settings.index'));

        $this->get(route('admin.privacy-settings.index'))
            ->assertOk()
            ->assertSee('365')
            ->assertSee('admin@gmail.com');

        $this->assertDatabaseHas('privacy_settings', [
            'id' => 1,
            'retention_days' => 365,
            'contact_name' => 'admin',
            'contact_email' => 'admin@gmail.com',
        ]);
        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $admin->id,
            'action' => 'privacy.updated',
            'subject_type' => 'PrivacySetting',
            'subject_id' => 1,
        ]);
    }

    public function test_retention_command_anonymizes_expired_identities_and_keeps_results(): void
    {
        PrivacySetting::create([
            'retention_days' => 30,
            'contact_name' => 'admin',
            'contact_email' => 'admin@gmail.com',
        ]);
        $dimension = Dimension::create([
            'name' => 'Literasi Dasar',
            'slug' => 'literasi-dasar',
            'weight' => 100,
            'is_active' => true,
        ]);
        $assessment = Assessment::create(['title' => 'Asesmen PELITA', 'status' => 'draft']);
        $question = Question::create([
            'assessment_id' => $assessment->id,
            'dimension_id' => $dimension->id,
            'question_text' => 'Pertanyaan?',
            'type' => 'multiple_choice',
            'weight' => 1,
            'position' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);
        $option = QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Jawaban',
            'is_correct' => true,
            'position' => 1,
        ]);

        $expiredParticipant = Participant::create([
            'name' => 'Peserta Lama',
            'email' => 'lama@example.com',
        ]);
        $expiredAttempt = AssessmentAttempt::create([
            'uuid' => '00000000-0000-4000-8000-000000000001',
            'participant_id' => $expiredParticipant->id,
            'assessment_id' => $assessment->id,
            'status' => 'completed',
            'started_at' => now()->subDays(41),
            'completed_at' => now()->subDays(40),
            'total_score' => 75,
        ]);
        $answer = Answer::forceCreate([
            'attempt_id' => $expiredAttempt->id,
            'question_id' => $question->id,
            'question_option_id' => $option->id,
            'score' => 75,
        ]);

        $recentParticipant = Participant::create([
            'name' => 'Peserta Baru',
            'email' => 'baru@example.com',
        ]);
        AssessmentAttempt::create([
            'uuid' => '00000000-0000-4000-8000-000000000002',
            'participant_id' => $recentParticipant->id,
            'assessment_id' => $assessment->id,
            'status' => 'completed',
            'started_at' => now()->subDays(21),
            'completed_at' => now()->subDays(20),
        ]);

        $activeParticipant = Participant::create([
            'name' => 'Peserta Aktif',
            'email' => 'aktif@example.com',
        ]);
        AssessmentAttempt::create([
            'uuid' => '00000000-0000-4000-8000-000000000003',
            'participant_id' => $activeParticipant->id,
            'assessment_id' => $assessment->id,
            'status' => 'in_progress',
            'started_at' => now()->subDays(60),
        ]);

        $this->artisan('pelita:anonymize-expired-participants')->assertExitCode(0);

        $this->assertTrue($expiredParticipant->fresh()->is_anonymized);
        $this->assertSame('Peserta anonim #'.$expiredParticipant->id, $expiredParticipant->fresh()->name);
        $this->assertSame('peserta-'.$expiredParticipant->id.'@anonim.invalid', $expiredParticipant->fresh()->email);
        $this->assertNotNull($expiredParticipant->fresh()->anonymized_at);
        $this->assertDatabaseHas('answers', ['id' => $answer->id, 'score' => '75.00']);
        $this->assertDatabaseHas('assessment_attempts', ['id' => $expiredAttempt->id, 'total_score' => '75.00']);
        $this->assertFalse($recentParticipant->fresh()->is_anonymized);
        $this->assertFalse($activeParticipant->fresh()->is_anonymized);
    }

    public function test_retention_command_refuses_to_run_without_a_configured_policy(): void
    {
        $this->artisan('pelita:anonymize-expired-participants')
            ->expectsOutput('Pengaturan retensi dan kontak privasi harus disimpan terlebih dahulu.')
            ->assertExitCode(1);
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
