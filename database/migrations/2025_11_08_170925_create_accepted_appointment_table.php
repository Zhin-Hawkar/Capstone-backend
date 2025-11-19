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
        Schema::create('accepted_appointment', function (Blueprint $table) {
            $table->id();
            $table->integer("patientId")->nullable();//
            $table->integer("doctorId")->nullable();//
            $table->integer("hospitalId")->nullable();//
            $table->string("firstName")->nullable();//
            $table->string("lastName")->nullable();//
            $table->string("image")->nullable();//
            $table->string("doctorFirstName")->nullable();//
            $table->string("doctorLastName")->nullable();//
            $table->string("doctorImage")->nullable();//
            $table->string("hospitalName")->nullable();//
            $table->string("hospitalLocation")->nullable();//
            $table->integer("age")->nullable();//
            $table->string("gender")->nullable();//
            $table->string('email')->unique();//
            $table->string("department")->nullable();//
            $table->string("help")->nullable();//
            $table->string("medical_record")->nullable();//
            $table->dateTime("date_time")->nullable();//
            $table->string("status")->default("pending");//
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accepted_appointment');
    }
};
