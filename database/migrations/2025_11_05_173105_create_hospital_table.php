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
            $table->string('hospitalName')->nullable(); //
            $table->integer('hospitalCode')->nullable(); //
            $table->string('location')->nullable(); //
            $table->integer('licenseId')->nullable(); //
            $table->string('type')->nullable(); //
            $table->string('phoneNumber')->nullable(); //
            $table->string('website')->nullable(); //
            $table->json('departments')->nullable(); //
            $table->integer('workingHours')->nullable(); //
            $table->string('description')->nullable(); //
            $table->json('services')->nullable(); //
            $table->string('role')->nullable(); //
            $table->integer('numberOfBeds')->nullable(); //number of beds//
            $table->string('image')->nullable(); //
            $table->string('email')->unique(); //
            $table->string('remember_token')->nullable(); //
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
