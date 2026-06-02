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
        Schema::create('medicine_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()->constrained('family_members')->nullOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->time('schedule_time');
            $table->enum('frequency', ['daily', 'twice_daily', 'three_daily', 'weekly', 'monthly', 'as_needed'])->default('daily');
            $table->string('dosage_amount', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('family_member_id');
            $table->index('medicine_id');
            $table->index('schedule_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_schedules');
    }
};
