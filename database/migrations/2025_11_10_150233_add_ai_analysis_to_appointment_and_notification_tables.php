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
       Schema::table('appointment', function (Blueprint $table) {
        $table->longText('ai_analysis')->nullable();
    });

    Schema::table('patient_notification', function (Blueprint $table) {
        $table->longText('ai_analysis')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_and_notification_tables', function (Blueprint $table) {
            //
        });
    }
};
