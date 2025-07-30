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
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id('incident_id');
            // Foreign key to users table
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->string('guard_name')->nullable();
            $table->text('description')->nullable();
            $table->text('what')->nullable();
            $table->string('who')->nullable();
            $table->string('where')->nullable();
            $table->string('when')->nullable();
            $table->text('how')->nullable();
            $table->text('why')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
