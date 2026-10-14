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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // INV-2026-0001
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained('admissions')->nullOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('balance_due', 12, 2)->default(0.00);
            $table->string('payment_status', 30)->default('unpaid'); // unpaid, partial, paid, refunded
            $table->string('payment_method', 40)->nullable(); // cash, credit_card, bank_transfer, insurance
            $table->decimal('insurance_claimed_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('generated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('item_type', 40); // consultation, medication, lab_test, bed_charge, procedure
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
