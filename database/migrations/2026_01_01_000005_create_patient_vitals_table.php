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
        Schema::create('patient_vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->decimal('temperature', 4, 1)->nullable(); // in Celsius, e.g. 37.2
            $table->integer('blood_pressure_systolic')->nullable(); // mmHg, e.g. 120
            $table->integer('blood_pressure_diastolic')->nullable(); // mmHg, e.g. 80
            $table->integer('heart_rate')->nullable(); // bpm, e.g. 72
            $table->integer('respiratory_rate')->nullable(); // bpm, e.g. 16
            $table->integer('oxygen_saturation')->nullable(); // SpO2 %, e.g. 98
            $table->decimal('weight', 5, 2)->nullable(); // kg, e.g. 70.5
            $table->decimal('height', 5, 2)->nullable(); // cm, e.g. 175.0
            $table->decimal('bmi', 4, 1)->nullable(); // BMI calculation
            $table->string('triage_level', 30)->default('non_urgent'); // resuscitation, emergency, urgent, semi_urgent, non_urgent
            $table->text('clinical_notes')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_vitals');
    }
};
