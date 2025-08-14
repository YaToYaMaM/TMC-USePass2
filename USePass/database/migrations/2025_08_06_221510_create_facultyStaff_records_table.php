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
        Schema::create('facultyStaff_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->string('faculty_id');
            $table->foreign('faculty_id')
                ->references('faculty_id')->on('facultyStaff')
                ->onDelete('cascade');

            $table->timestamp('record_in')->nullable();
            $table->timestamp('record_out')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_staff_records');
    }
};
