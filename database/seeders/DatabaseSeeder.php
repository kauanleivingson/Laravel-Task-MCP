<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminPassword = config('app.seed_admin.password');

        if (! is_string($adminPassword) || $adminPassword === '') {
            throw new RuntimeException('Set SEED_ADMIN_PASSWORD before running the database seeder.');
        }

        $admin = User::firstOrCreate(
            ['email' => config('app.seed_admin.email')],
            [
                'name' => config('app.seed_admin.name'),
                'password' => $adminPassword,
            ],
        );

        $admin->syncRoles('admin');
    }
}
