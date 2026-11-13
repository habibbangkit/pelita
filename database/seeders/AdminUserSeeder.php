<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('PELITA_ADMIN_EMAIL');
        $password = env('PELITA_ADMIN_PASSWORD');

        if (! $email && ! $password) {
            $this->command?->info('Admin seeder dilewati. Isi PELITA_ADMIN_EMAIL dan PELITA_ADMIN_PASSWORD untuk membuat akun admin.');

            return;
        }

        if (! $email || ! $password || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('PELITA_ADMIN_EMAIL dan PELITA_ADMIN_PASSWORD harus diisi dengan nilai yang valid.');
        }

        $user = User::query()->firstOrNew(['email' => Str::lower($email)]);
        $user->forceFill([
            'name' => env('PELITA_ADMIN_NAME', 'PELITA Admin'),
            'password' => $password,
            'is_admin' => true,
        ])->save();
    }
}
