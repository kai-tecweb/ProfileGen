<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 初期ユーザーを作成
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('初期ユーザーを作成しました:');
        $this->command->info('メールアドレス: admin@example.com');
        $this->command->info('パスワード: password');
    }
}
