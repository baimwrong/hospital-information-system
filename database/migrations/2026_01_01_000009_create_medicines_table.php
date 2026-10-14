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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // MED-0001
            $table->string('brand_name');
            $table->string('generic_name');
            $table->string('category', 60); // Antibiotic, Analgesic, Cardiovascular, etc.
            $table->string('dosage_form', 40); // Tablet, Capsule, Syrup, Injection, IV
            $table->string('strength', 50); // 500mg, 10mg/ml
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('purchase_cost', 10, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_alert')->default(10);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('storage_condition')->nullable();
            $table->string('status', 30)->default('in_stock'); // in_stock, low_stock, out_of_stock, expired
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
