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
        Schema::create('students_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->string('students_id');
            $table->foreign('students_id')
                ->references('students_id')->on('students')
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
        Schema::dropIfExists('students_records');
    }
};
