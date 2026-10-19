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
        // Add soft deletes and indexes to patients
        Schema::table('patients', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('phone');
            $table->index('national_id');
            $table->index('status');
            $table->index(['last_name', 'first_name']);
        });

        // Add soft deletes and indexes to doctors
        Schema::table('doctors', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('specialization');
            $table->index('status');
        });

        // Add soft deletes and indexes to medical_records
        Schema::table('medical_records', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('diagnosis_icd10_code');
        });

        // Add soft deletes and indexes to prescriptions
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('status');
        });

        // Add soft deletes and indexes to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('patient_id');
            $table->index('invoice_date');
            $table->index('payment_status');
        });

        // Add indexes to appointments
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['doctor_id', 'appointment_date', 'appointment_time']);
            $table->index(['patient_id', 'appointment_date']);
            $table->index('status');
        });

        // Add indexes to medicines
        Schema::table('medicines', function (Blueprint $table) {
            $table->index('brand_name');
            $table->index('generic_name');
            $table->index('category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex(['brand_name']);
            $table->dropIndex(['generic_name']);
            $table->dropIndex(['category']);
            $table->dropIndex(['status']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['doctor_id', 'appointment_date', 'appointment_time']);
            $table->dropIndex(['patient_id', 'appointment_date']);
            $table->dropIndex(['status']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['payment_status']);
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['diagnosis_icd10_code']);
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['specialization']);
            $table->dropIndex(['status']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['phone']);
            $table->dropIndex(['national_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['last_name', 'first_name']);
        });
    }
};
