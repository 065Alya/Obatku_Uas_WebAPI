<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * api_keys — per-user API keys for third-party integrations
 * (e.g., OpenFDA lookup, caregiver app connections).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('name', 100);          // human-readable label, e.g. "OpenFDA Integration"
            $table->string('key', 80)->unique();  // hashed API key

            $table->json('abilities')->nullable(); // e.g. ["medicines:read","schedules:read"]
            $table->json('allowed_ips')->nullable(); // IP whitelist, null = any

            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // null = never expires

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index(['user_id', 'is_active'], 'api_keys_user_active_idx');
            $table->index('expires_at',             'api_keys_expires_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
