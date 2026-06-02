<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * consumptions — granular per-dose intake log.
 *
 * Why separate from schedule_logs?
 *   schedule_logs tracks whether a SCHEDULED dose was taken/skipped/missed.
 *   consumptions tracks actual quantity consumed (for stock deduction and
 *   adherence analytics), including ad-hoc doses not tied to a schedule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('medicine_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Optional link to the schedule that triggered this intake
            $table->foreignId('medicine_schedule_id')
                  ->nullable()
                  ->constrained('medicine_schedules')
                  ->nullOnDelete();

            // Optional link to the schedule_log entry for this session
            $table->foreignId('schedule_log_id')
                  ->nullable()
                  ->constrained('schedule_logs')
                  ->nullOnDelete();

            // For family-member medications
            $table->foreignId('family_member_id')
                  ->nullable()
                  ->constrained('family_members')
                  ->nullOnDelete();

            $table->decimal('quantity', 8, 2)->default(1); // how many units taken
            $table->string('unit', 50)->default('tablet');  // tablet, ml, drop, etc.

            $table->enum('status', ['taken', 'skipped', 'missed'])->default('taken');

            $table->text('notes')->nullable();

            // Exact datetime of consumption (different from schedule_time)
            $table->timestamp('consumed_at');

            // Offline sync support — true if created while device was offline
            $table->boolean('is_synced')->default(true);
            $table->string('offline_id')->nullable()->unique(); // client-generated UUID

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index(['user_id', 'consumed_at'],          'consumptions_user_date_idx');
            $table->index(['medicine_id', 'consumed_at'],      'consumptions_med_date_idx');
            $table->index(['user_id', 'status'],               'consumptions_user_status_idx');
            $table->index('is_synced',                         'consumptions_synced_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumptions');
    }
};
