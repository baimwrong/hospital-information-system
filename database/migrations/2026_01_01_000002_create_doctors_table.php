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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('specialization');
            $table->string('license_number')->unique();
            $table->string('qualification')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(0.00);
            $table->string('room_number')->nullable();
            $table->text('biography')->nullable();
            $table->json('available_days')->nullable();
            $table->string('status')->default('active'); // active, on_leave, inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
