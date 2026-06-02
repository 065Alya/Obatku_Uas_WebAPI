<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing soft-delete columns.
 *
 * The models use SoftDeletes but the original migration ran before the
 * softDeletes() call was added. This migration adds deleted_at where missing.
 */
return new class extends Migration
{
    private array $tables = [
        'medicine_schedules',
        'family_members',
        'families',
        'consumptions',
        'schedule_logs',
        'health_articles',
        'activity_logs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
