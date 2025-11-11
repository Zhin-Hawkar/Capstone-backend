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
        Schema::create('patient_notification', function (Blueprint $table) {
            $table->id();
            $table->integer("patientId");
            $table->integer("doctorId");
            $table->string("firstName")->nullable();
            $table->string("lastName")->nullable();
            $table->integer("age")->nullable();
            $table->string("gender")->nullable();
            $table->string('email')->unique();
            $table->string('help')->unique();
            $table->longText('ai_analysis')->unique();
            $table->string("department")->nullable();
            $table->dateTime("date_time")->nullable();
            $table->string("status")->default("pending");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_notification');
    }
};
