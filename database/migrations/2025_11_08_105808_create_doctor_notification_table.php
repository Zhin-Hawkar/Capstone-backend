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
        Schema::create('doctor_notification', function (Blueprint $table) {
            $table->id();
            $table->integer("patientId");
            $table->string("firstName");
            $table->string("lastName");
            $table->integer("age");
            $table->string("gender");
            $table->integer("yearsofexperience");
            $table->string("description");
            $table->string('email')->unique();
            $table->string("department");
            $table->string("hospital");
            $table->string("specialization");
            $table->json("qualification");
            $table->dateTime("date_time");
            $table->string("status")->default("pending");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_notification');
    }
};
