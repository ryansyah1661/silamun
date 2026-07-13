<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create pivot table for species found in each survey.
     */
    public function up(): void
    {
        Schema::create('survey_species', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_id');
            $table->unsignedBigInteger('species_id');
            $table->decimal('coverage_percent', 5, 2); // species-specific coverage %
            $table->decimal('density', 8, 2)->nullable(); // individuals per m²
            $table->boolean('is_dominant')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('survey_id')
                  ->references('id')
                  ->on('surveys')
                  ->onDelete('cascade');

            $table->foreign('species_id')
                  ->references('id')
                  ->on('species')
                  ->onDelete('cascade');

            // Unique constraint: one species entry per survey
            $table->unique(['survey_id', 'species_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_species');
    }
};
