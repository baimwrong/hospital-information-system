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
        Schema::create('bulk_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('entity_type', 50); // patients, doctors, medicines, lab_test_types, departments
            $table->string('original_filename');
            $table->integer('total_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->string('status', 30)->default('pending'); // pending, processing, completed, completed_with_errors, failed
            $table->json('error_summary')->nullable(); // JSON list of errors: row, field, message
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_imports');
    }
};
