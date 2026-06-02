<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * offline_sync_queue — PWA offline operations queue.
 *
 * When the device is offline, mutations (log intake, add medicine, etc.)
 * are queued locally in IndexedDB. On reconnect, the service worker
 * replays them by POST-ing each entry to /api/sync. The server records
 * the result here for idempotency and audit purposes.
 *
 * Status lifecycle:  pending → processing → synced | failed
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_sync_queue', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Client-generated UUID used for idempotency
            $table->uuid('client_id')->unique();

            // The model being mutated, e.g. "consumption", "schedule_log"
            $table->string('entity_type', 60);

            // The action to perform, e.g. "create", "update", "delete"
            $table->string('action', 20);

            // JSON payload of the operation (validated on server before apply)
            $table->json('payload');

            // HTTP status code returned when sync was attempted
            $table->unsignedSmallInteger('http_status')->nullable();

            // Error message if sync failed
            $table->text('error_message')->nullable();

            $table->enum('status', ['pending', 'processing', 'synced', 'failed'])
                  ->default('pending');

            // Number of sync attempts (for back-off / dead-letter logic)
            $table->unsignedTinyInteger('attempts')->default(0);

            // Timestamp when the operation was performed offline on the client
            $table->timestamp('performed_at');

            // Timestamp when server successfully applied the operation
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index(['user_id', 'status'],    'sync_queue_user_status_idx');
            $table->index('status',                 'sync_queue_status_idx');
            $table->index('performed_at',           'sync_queue_performed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_sync_queue');
    }
};
