<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create survey validations table for review/approval audit log.
     */
    public function up(): void
    {
        Schema::create('survey_validations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_id');
            $table->unsignedBigInteger('validator_id');
            $table->string('action'); // 'approved', 'rejected'
            $table->text('comments')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('survey_id')
                  ->references('id')
                  ->on('surveys')
                  ->onDelete('cascade');

            $table->foreign('validator_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_validations');
    }
};
