<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create species catalog table for seagrass species.
     */
    public function up(): void
    {
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // scientific name, e.g., 'Enhalus acoroides'
            $table->string('local_name')->nullable(); // Indonesian common name
            $table->string('family'); // e.g., 'Hydrocharitaceae'
            $table->string('order')->default('Alismatales');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('morphology')->nullable(); // morphological characteristics
            $table->text('habitat')->nullable();
            $table->decimal('depth_range_min', 4, 1)->nullable(); // meters
            $table->decimal('depth_range_max', 4, 1)->nullable(); // meters
            $table->string('iucn_status')->nullable(); // LC, NT, VU, EN, CR
            $table->decimal('carbon_factor', 8, 6)->default(0.057); // tC/Ha factor
            $table->string('photo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('species');
    }
};
