<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('discord_name', 255)->unique()->comment('Discord名（ログインID）');
            $table->string('email', 255)->unique()->comment('メールアドレス');
            $table->string('password', 255)->comment('ハッシュ化されたパスワード');
            $table->rememberToken()->comment('ログイン記憶トークン');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('承認状態');
            $table->enum('student_status', ['active', 'inactive', 'banned', 'no_contact'])->default('active')->comment('活動状況');
            $table->timestamp('approved_at')->nullable()->comment('承認日時');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->comment('承認した管理者ID');
            $table->text('memo')->nullable()->comment('メモ・備考');
            $table->timestamps();
            
            // インデックス
            $table->index('status');
            $table->index('student_status');
            $table->index('discord_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
