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
        $connection = Schema::getConnection();
        
        // カラムが存在しない場合のみ追加
        if (!Schema::hasColumn('consultations', 'student_id')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->foreignId('student_id')->nullable()->after('user_id')->comment('生徒ID（studentsテーブル参照）');
            });
        }

        // インデックスが存在しない場合のみ追加（SQLで確認）
        $indexExists = $connection->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = DATABASE() 
             AND table_name = 'consultations' 
             AND index_name = 'consultations_student_id_index'"
        );
        
        if ($indexExists->count == 0) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->index('student_id');
            });
        }

        // 外部キー制約が存在しない場合のみ追加（SQLで確認）
        $foreignKeyExists = $connection->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.key_column_usage 
             WHERE table_schema = DATABASE() 
             AND table_name = 'consultations' 
             AND column_name = 'student_id' 
             AND referenced_table_name IS NOT NULL"
        );
        
        if ($foreignKeyExists->count == 0) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropIndex(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};
