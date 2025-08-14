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
        Schema::create('spot_reports', function (Blueprint $table) {
            $table->id(); // This creates an 'id' column as primary key

            // Foreign key to users table
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            // Report fields
            $table->text('findings');
            $table->string('team_leader');
            $table->string('guard_name');
            $table->string('ssu_head')->nullable();
            $table->text('action_taken');
            $table->string('department_representative');
            $table->string('location')->nullable();
            $table->json('spotPicture')->nullable();
            $table->time('time')->nullable();
            $table->date('date')->nullable();
            // Printing tracking
            $table->boolean('is_printed')->default(false);
            $table->timestamp('printed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spot_reports');
    }
};
