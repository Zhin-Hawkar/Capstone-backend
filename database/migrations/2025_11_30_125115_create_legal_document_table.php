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
        Schema::create('legal_document', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->integer('doctorId')->nullable();
            $table->integer('hospitalId')->nullable();
            $table->string('fileName')->nullable();
            $table->string('legalDocument')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_document');
    }
};
