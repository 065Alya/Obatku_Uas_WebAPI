<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_schedule_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['taken', 'skipped', 'missed'])->default('taken');
            $table->timestamp('taken_at')->nullable();
            $table->string('skipped_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['medicine_schedule_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_logs');
    }
};
