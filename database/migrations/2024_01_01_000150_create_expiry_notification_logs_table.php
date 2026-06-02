<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * expiry_notification_logs — deduplication log for expiry alerts.
 *
 * Prevents the scheduler from re-sending the same expiry notification
 * every time the artisan command runs. A notification is only re-sent
 * after `resend_after` has passed (defaults to 7 days).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expiry_notification_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('medicine_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // The notification channel used: 'database', 'mail', 'sms', 'push'
            $table->string('channel', 20)->default('database');

            // Snapshot of expiry at notification time
            $table->date('expiry_date');

            // Days-before-expiry threshold that triggered this alert (e.g. 30, 7, 1)
            $table->unsignedTinyInteger('days_threshold');

            // When to allow re-sending; null = never re-send
            $table->timestamp('resend_after')->nullable();

            $table->timestamp('sent_at')->useCurrent();

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->unique(
                ['user_id', 'medicine_id', 'days_threshold', 'channel'],
                'expiry_notif_unique_idx'
            );
            $table->index(['medicine_id', 'expiry_date'], 'expiry_notif_med_date_idx');
            $table->index('resend_after',                 'expiry_notif_resend_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expiry_notification_logs');
    }
};
