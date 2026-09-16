<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $t->string('roll_number', 50)->unique();
            $t->string('registration_number', 100)->nullable()->unique();
            $t->string('batch', 50);
            $t->string('academic_year', 20);
            foreach (['college', 'course', 'department'] as $f) {
                $t->string($f, 150)->nullable();
            }
            $t->unsignedSmallInteger('joining_year')->nullable();
            $t->string('phone', 30)->nullable();
            $t->date('date_of_birth')->nullable();
            $t->string('gender', 30)->nullable();
            $t->text('address')->nullable();
            $t->text('bio')->nullable();
            $t->string('avatar_path')->nullable();
            $t->timestamps();
            $t->index(['academic_year', 'batch']);
        });
        Schema::create('patients', function (Blueprint $t): void {
            $t->id();
            $t->string('case_number', 40)->unique();
            $t->string('display_name', 120);
            $t->string('data_classification', 20)->default('synthetic');
            $t->string('gender', 30);
            $t->unsignedSmallInteger('age_years');
            $t->date('admission_date');
            foreach (['chief_complaint', 'presenting_symptoms', 'history_of_present_illness', 'past_medical_history', 'family_history', 'medication_history', 'allergy_history', 'social_history', 'examination_findings', 'working_diagnosis', 'confirmed_diagnosis', 'investigations', 'management_notes', 'clinical_notes'] as $f) {
                $t->text($f)->nullable();
            }
            $t->string('condition', 150)->nullable();
            $t->json('additional_context')->nullable();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $t->timestamps();
            $t->index(['admission_date', 'id']);
            $t->index(['condition', 'admission_date']);
        });
        Schema::create('patient_encounters', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('patient_id')->constrained()->restrictOnDelete();
            $t->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $t->dateTime('attended_at');
            $t->text('summary');
            foreach (['symptoms_observed', 'examination_findings', 'assessment', 'learning_notes'] as $f) {
                $t->text($f)->nullable();
            }
            $t->timestamp('locked_at')->nullable();
            $t->timestamps();
            $t->index(['student_id', 'attended_at', 'id'], 'enc_student_attended');
            $t->index(['patient_id', 'attended_at', 'id'], 'enc_patient_attended');
            $t->index(['attended_at', 'id']);
        });
        Schema::create('encounter_images', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('encounter_id')->constrained('patient_encounters')->restrictOnDelete();
            $t->string('file_path')->unique();
            $t->string('original_name')->nullable();
            $t->string('mime_type', 100);
            $t->unsignedBigInteger('file_size');
            $t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $t->timestamps();
            $t->index(['encounter_id', 'created_at']);
        });
        Schema::create('hod_reviews', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('encounter_id')->constrained('patient_encounters')->restrictOnDelete();
            $t->foreignId('hod_id')->constrained('users')->restrictOnDelete();
            $t->text('comment');
            $t->timestamps();
            $t->index(['encounter_id', 'created_at', 'id'], 'hod_enc_created');
            $t->index(['hod_id', 'created_at']);
        });
        Schema::create('ai_reviews', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('encounter_id')->constrained('patient_encounters')->restrictOnDelete();
            $t->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $t->uuid('request_key')->unique();
            $t->string('provider', 40);
            $t->string('model', 150);
            $t->string('prompt_version', 40);
            $t->string('status', 20)->default('pending');
            $t->json('input_snapshot');
            $t->char('input_hash', 64);
            $t->json('structured_response')->nullable();
            $t->string('error_code', 60)->nullable();
            $t->timestamp('privacy_confirmed_at');
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->index(['encounter_id', 'created_at', 'id'], 'ai_enc_created');
            $t->index(['requested_by', 'created_at', 'id'], 'ai_actor_created');
            $t->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['ai_reviews', 'hod_reviews', 'encounter_images', 'patient_encounters', 'patients', 'student_profiles'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
