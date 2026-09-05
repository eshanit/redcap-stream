<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data6_source_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('project_id');
            $table->string('redcap_record', 100);
            $table->timestamps();
            $table->unique(['project_id', 'redcap_record']);
        });

        Schema::create('data6_patients', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('data6_patient_source_records', function (Blueprint $table) {
            $table->foreignId('patient_id')->constrained('data6_patients')->cascadeOnDelete();
            $table->foreignId('source_record_id')->constrained('data6_source_records')->cascadeOnDelete();
            $table->string('match_method')->default('source_record');
            $table->decimal('match_confidence', 5, 4)->nullable();
            $table->string('review_status')->default('pending');
            $table->timestamps();
            $table->primary(['patient_id', 'source_record_id']);
        });

        Schema::create('data6_encounters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_record_id')->constrained('data6_source_records')->cascadeOnDelete();
            $table->unsignedInteger('project_id');
            $table->string('redcap_record', 100);
            $table->unsignedInteger('event_id')->nullable();
            $table->unsignedInteger('arm_id')->nullable();
            $table->string('facility', 100)->nullable();
            $table->string('instrument', 100);
            $table->string('service', 100);
            $table->string('subject_type', 30)->default('client');
            $table->smallInteger('raw_instance')->nullable();
            $table->unsignedSmallInteger('normalized_instance');
            $table->date('service_date')->nullable();
            $table->string('date_source', 100)->nullable();
            $table->string('source_key', 64)->unique();
            $table->json('source_fields')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'redcap_record']);
            $table->index(['service', 'service_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data6_encounters');
        Schema::dropIfExists('data6_patient_source_records');
        Schema::dropIfExists('data6_patients');
        Schema::dropIfExists('data6_source_records');
    }
};