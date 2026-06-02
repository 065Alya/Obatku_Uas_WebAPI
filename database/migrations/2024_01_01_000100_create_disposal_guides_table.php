<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposal_guides', function (Blueprint $table) {
            $table->id();
            $table->string('medicine_form');       // tablet, sirup, kapsul, salep, injeksi, dll
            $table->string('title');
            $table->text('description');
            $table->json('steps');                 // array of step strings
            $table->string('icon')->nullable();    // lucide icon name
            $table->string('color')->default('#1D9E75');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposal_guides');
    }
};
