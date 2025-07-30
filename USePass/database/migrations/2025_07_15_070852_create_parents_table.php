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
        Schema::create('parents', function (Blueprint $table) {
            $table->id('parent_id');

            $table->string('students_id');
            $table->foreign('students_id')
                ->references('students_id')->on('students')
                ->onDelete('cascade');

            $table->string('parent_first_name', 100)->nullable();
            $table->string('parent_last_name', 100)->nullable();
            $table->string('parent_middle_initial', 50)->nullable();
            $table->string('parent_relation', 50)->nullable();

            $table->string('parent_email', 100)->nullable();
            $table->string('parent_phone_num', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
