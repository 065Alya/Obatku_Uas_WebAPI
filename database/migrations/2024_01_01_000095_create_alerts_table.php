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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            /**
             * Alert type determines the business context:
             *   - interaction : potential drug–drug interaction detected
             *   - stock       : medicine stock has reached or fallen below threshold
             *   - reminder    : scheduled-dose reminder that was not logged
             */
            $table->enum('type', ['interaction', 'stock', 'reminder']);

            /**
             * Severity level for triage / UI colour coding:
             *   - info    : informational only, no immediate action needed
             *   - warning : should be reviewed soon
             *   - danger  : requires immediate attention
             */
            $table->enum('severity', ['info', 'warning', 'danger'])->default('info');

            $table->text('message');

            $table->boolean('is_read')->default(false);

            /**
             * Optional polymorphic reference to the originating entity.
             * e.g. Medicine, MedicineSchedule, MedicineInteraction
             */
            $table->nullableMorphs('alertable'); // alertable_type, alertable_id

            $table->timestamps();

            // ── Indexes ──────────────────────────────────────────────────────
            $table->index(['user_id', 'is_read'],   'alerts_user_read_idx');
            $table->index(['user_id', 'type'],       'alerts_user_type_idx');
            $table->index(['user_id', 'severity'],   'alerts_user_severity_idx');
            $table->index('created_at',              'alerts_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
