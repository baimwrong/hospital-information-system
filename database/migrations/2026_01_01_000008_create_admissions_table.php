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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete(); // Attending doctor
            $table->foreignId('bed_id')->constrained('beds')->cascadeOnDelete();
            $table->timestamp('admission_date')->useCurrent();
            $table->timestamp('discharge_date')->nullable();
            $table->text('admission_reason');
            $table->string('status', 30)->default('admitted'); // admitted, discharged, transferred
            $table->text('discharge_summary')->nullable();
            $table->string('condition_at_discharge', 50)->nullable(); // recovered, improved, referred, against_medical_advice
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
