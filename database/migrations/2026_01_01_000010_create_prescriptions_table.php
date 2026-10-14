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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->nullOnDelete();
            $table->string('status', 30)->default('pending'); // pending, partially_dispensed, dispensed, cancelled
            $table->foreignId('dispensed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dispensed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('dosage'); // 1 tablet 3x daily
            $table->string('frequency')->nullable(); // Every 8 hours
            $table->integer('duration_days')->default(5);
            $table->integer('quantity_prescribed');
            $table->integer('quantity_dispensed')->default(0);
            $table->text('instructions')->nullable(); // Take after food
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->string('status', 30)->default('pending'); // pending, dispensed, out_of_stock
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
    }
};
