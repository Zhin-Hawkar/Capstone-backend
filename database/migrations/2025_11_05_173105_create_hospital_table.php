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
        Schema::create('hospital', function (Blueprint $table) {
            $table->id();
            $table->string('hospitalName'); //
            $table->integer('hospitalCode'); //
            $table->string('location'); //
            $table->integer('licenseId'); //
            $table->string('type'); //
            $table->string('phoneNumber'); //
            $table->string('website')->nullable(); //
            $table->json('departments'); //
            $table->integer('workingHours'); //
            $table->string('description'); //
            $table->json('services'); //
            $table->string('role'); //
            $table->integer('numberOfBeds'); //number of beds//
            $table->string('image')->nullable(); //
            $table->string('email')->unique(); //
            $table->string('remember_token'); //
            $table->string('password'); //
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital');
    }
};
