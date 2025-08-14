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
        Schema::create('facultyStaff', function (Blueprint $table) {
            $table->string('faculty_id')->primary();
            $table->string('faculty_first_name', 100);
            $table->string('faculty_last_name', 100);
            $table->string('faculty_middle_initial', 50)->nullable();
            $table->string('faculty_gender', 50);
            $table->enum('faculty_college', [
                'College of Teacher Education and Technology (CTET)',
                'School of Medicine (SoM)',
                'College of Agriculture and Related Sciences (CARS)'
            ]);
            $table->string('faculty_department', 150);
            $table->enum('faculty_unit', ['Tagum', 'Mabini'])->default('Tagum');
            $table->string('faculty_email', 100)->unique();
            $table->string('faculty_phone_num', 20);
            $table->string('faculty_profile_image')->nullable();
            $table->timestamps();

            $table->index('faculty_college');
            $table->index('faculty_department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty');
    }
};
