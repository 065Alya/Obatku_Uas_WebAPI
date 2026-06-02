<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * push_subscriptions — stores Web Push API (VAPID) subscriptions.
 *
 * Each browser/device that grants push permission gets one row.
 * A single user can have multiple subscriptions (phone, tablet, PC).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Push endpoint URL provided by the browser (unique per device/browser)
            $table->text('endpoint');

            // VAPID keys
            $table->text('p256dh_key');    // client's EC public key
            $table->text('auth_token');    // shared auth secret

            // Metadata for display / management
            $table->string('device_name', 100)->nullable();  // e.g. "iPhone 15 – Safari"
            $table->string('user_agent', 512)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_notified_at')->nullable();

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index(['user_id', 'is_active'], 'push_sub_user_active_idx');
            // Endpoint uniqueness per user (a user can't register the same device twice)
            $table->unique(['user_id', 'endpoint'], 'push_sub_unique_endpoint');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
