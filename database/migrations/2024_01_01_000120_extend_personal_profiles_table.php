<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the slim personal_profiles migration with a full health-profile schema.
 * Adds blood_type, height/weight, allergies, chronic_diseases, and emergency contacts.
 *
 * NOTE: This migration ALTERS the existing table rather than recreating it,
 * so it is safe to run against an already-migrated database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_profiles', function (Blueprint $table) {
            // Add health columns that were missing from the original migration
            $table->enum('blood_type', ['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])
                  ->nullable()
                  ->after('gender');

            $table->decimal('height_cm', 5, 1)->nullable()->after('blood_type');
            $table->decimal('weight_kg', 5, 1)->nullable()->after('height_cm');

            $table->text('allergies')->nullable()->after('weight_kg');
            $table->text('chronic_diseases')->nullable()->after('allergies');

            $table->string('emergency_contact_name', 255)->nullable()->after('chronic_diseases');
            $table->string('emergency_contact_phone', 20)->nullable()->after('emergency_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('personal_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'blood_type',
                'height_cm',
                'weight_kg',
                'allergies',
                'chronic_diseases',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });
    }
};
