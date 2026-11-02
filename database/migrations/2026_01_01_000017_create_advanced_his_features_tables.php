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
        // 1. Atomic Sequence Generator Table for high-concurrency collision-free identifiers
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. mrn_2026, apt_2026, enc_2026, adm_2026, rx_2026, lab_2026, inv_2026, pay_2026, med
            $table->unsignedBigInteger('current_value')->default(0);
            $table->timestamps();
        });

        // 2. Pharmacy Multi-Batch / Lot Expiry (FEFO) Table
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->integer('quantity')->default(0);
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->string('status', 30)->default('active'); // active, depleted, expired
            $table->timestamps();
            $table->softDeletes();

            $table->index(['medicine_id', 'status', 'expiry_date']);
        });

        // 3. Clinical Documents & File Attachments Table (PACS / Scans / Referrals)
        Schema::create('medical_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->nullOnDelete();
            $table->string('title');
            $table->string('document_type', 50)->default('general'); // referral_letter, lab_report, imaging_scan, pathology, discharge_summary, general
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 100)->default('application/octet-stream');
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('patient_id');
            $table->index('medical_record_id');
        });

        // 4. Clinical Triage Enhancements: National Early Warning Score (NEWS2)
        Schema::table('patient_vitals', function (Blueprint $table) {
            $table->integer('news2_score')->nullable()->after('bmi');
            $table->string('news2_risk_level', 30)->nullable()->after('news2_score'); // low, low_medium, medium, high
        });

        // 5. User Security Enhancement: Force password change on first login
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });

        Schema::table('patient_vitals', function (Blueprint $table) {
            $table->dropColumn(['news2_score', 'news2_risk_level']);
        });

        Schema::dropIfExists('medical_documents');
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('sequences');
    }
};
