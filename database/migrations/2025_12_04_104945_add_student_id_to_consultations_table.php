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
        // カラムが存在しない場合のみ追加
        if (!Schema::hasColumn('consultations', 'student_id')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->foreignId('student_id')->nullable()->after('user_id')->comment('生徒ID（studentsテーブル参照）');
            });
        }

        // インデックスが存在しない場合のみ追加
        Schema::table('consultations', function (Blueprint $table) {
            $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('consultations');
            $hasIndex = false;
            foreach ($indexes as $index) {
                if (in_array('student_id', $index->getColumns())) {
                    $hasIndex = true;
                    break;
                }
            }
            if (!$hasIndex) {
                $table->index('student_id');
            }
        });

        // 外部キー制約が存在しない場合のみ追加
        $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('consultations');
        $hasForeignKey = false;
        foreach ($foreignKeys as $foreignKey) {
            if (in_array('student_id', $foreignKey->getColumns())) {
                $hasForeignKey = true;
                break;
            }
        }

        if (!$hasForeignKey) {
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
