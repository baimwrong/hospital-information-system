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
        Schema::create('lab_test_types', function (Blueprint $table) {
            $table->id();
            $table->string('test_code')->unique(); // CBC-01
            $table->string('name');
            $table->string('category', 60); // Hematology, Biochemistry, Microbiology, Radiology
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('sample_type')->nullable(); // Whole Blood, Serum, Urine
            $table->text('reference_range')->nullable();
            $table->integer('turnaround_time_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lab_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // LAB-2026-0001
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->nullOnDelete();
            $table->text('clinical_notes')->nullable();
            $table->string('priority', 20)->default('routine'); // routine, urgent, stat
            $table->string('status', 30)->default('pending'); // pending, sample_collected, processing, completed, cancelled
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_request_id')->constrained('lab_requests')->cascadeOnDelete();
            $table->foreignId('lab_test_type_id')->constrained('lab_test_types')->cascadeOnDelete();
            $table->string('parameter_name');
            $table->string('result_value');
            $table->string('unit')->nullable();
            $table->string('reference_range')->nullable();
            $table->string('flag', 20)->default('normal'); // normal, high, low, critical
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_test_results');
        Schema::dropIfExists('lab_requests');
        Schema::dropIfExists('lab_test_types');
    }
};
