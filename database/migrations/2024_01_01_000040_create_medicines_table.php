<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic ownership (User or FamilyMember)
            $table->morphs('owner');
            
            $table->foreignId('category_id')->nullable()->constrained('medicine_categories')->nullOnDelete();
            
            $table->string('medicine_name');
            $table->string('generic_name')->nullable();
            $table->string('dosage', 100)->nullable();
            $table->string('unit', 50)->default('tablet'); 
            $table->string('form', 50)->default('oral');   
            $table->string('manufacturer')->nullable();
            $table->text('description')->nullable();
            $table->text('side_effects')->nullable();
            
            $table->integer('stock')->default(0);
            $table->integer('stock_alert')->default(5);
            
            $table->decimal('price', 12, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_type', 'owner_id', 'is_active']);
            $table->index('category_id');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
