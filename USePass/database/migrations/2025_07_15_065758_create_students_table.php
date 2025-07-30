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
        Schema::create('students', function (Blueprint $table) {
            $table->string('students_id')->primary();

            $table->string('students_first_name', 100)->nullable();
            $table->string('students_last_name', 100)->nullable();
            $table->string('students_middle_initial', 50)->nullable();
            $table->string('students_gender', 50)->nullable();
            $table->string('students_program', 100)->nullable();
            $table->string('students_major', 100)->nullable();
            $table->enum('students_unit', ['Tagum', 'Mabini'])->default('Tagum');

            $table->string('students_email', 100)->nullable();
            $table->string('students_phone_num', 20)->nullable();
            $table->string('students_profile_image')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
