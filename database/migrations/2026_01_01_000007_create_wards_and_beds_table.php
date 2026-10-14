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
        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 40)->default('general'); // general, icu, maternity, pediatric, surgical, vip, isolation
            $table->string('floor', 50)->nullable();
            $table->integer('capacity')->default(10);
            $table->string('gender_preference', 20)->default('mixed'); // mixed, male, female
            $table->decimal('daily_rate', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained('wards')->cascadeOnDelete();
            $table->string('bed_number');
            $table->string('bed_type', 40)->default('standard'); // standard, icu, pediatric, electric
            $table->string('status', 30)->default('available'); // available, occupied, cleaning, maintenance
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['ward_id', 'bed_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
        Schema::dropIfExists('wards');
    }
};
