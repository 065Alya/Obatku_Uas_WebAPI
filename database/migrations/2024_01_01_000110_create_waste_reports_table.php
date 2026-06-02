<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('medicine_name');       // snapshot in case medicine is deleted
            $table->string('medicine_form');       // tablet, sirup, dll
            $table->decimal('quantity', 8, 2);
            $table->string('unit');                // tablet, ml, gram, dll
            $table->string('disposal_method');     // pharmacy_return, household_trash, flush, etc.
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->date('disposed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_reports');
    }
};
