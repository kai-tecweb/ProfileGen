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
        Schema::table('clients', function (Blueprint $table) {
            $table->text('questionnaire_text')->nullable()->after('memo')->comment('送付した質問テキスト（参照用）');
            $table->text('answers_text')->nullable()->after('questionnaire_text')->comment('クライアントからのヒアリング回答（フリーテキスト）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['questionnaire_text', 'answers_text']);
        });
    }
};
