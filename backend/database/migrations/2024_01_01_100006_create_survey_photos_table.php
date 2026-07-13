<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create survey photos table for documentation images.
     */
    public function up(): void
    {
        Schema::create('survey_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('survey_id');
            $table->string('photo_path'); // storage path
            $table->string('photo_url')->nullable(); // CDN URL (Cloudinary/MinIO)
            $table->string('caption')->nullable();
            $table->string('photo_type')->default('quadrat'); // quadrat, transect, landscape, other
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            // Foreign key with cascade delete
            $table->foreign('survey_id')
                  ->references('id')
                  ->on('surveys')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_photos');
    }
};
