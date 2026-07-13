<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create surveys table — the core data collection table.
     */
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // auto-generated, e.g., 'SRV-2025-00001'
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('region_id')->nullable();
            $table->string('location_name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->date('survey_date');
            $table->string('sampling_method')->default('transek_kuadrat');
            $table->decimal('water_temperature', 4, 1)->nullable(); // Celsius
            $table->decimal('salinity', 5, 2)->nullable(); // ppt
            $table->decimal('water_depth', 4, 1)->nullable(); // meters
            $table->string('substrate_type')->nullable(); // pasir, lumpur, pasir_berlumpur, karang
            $table->decimal('total_coverage_percent', 5, 2); // 0.00 to 100.00
            $table->string('health_status'); // sehat, kurang_sehat, miskin — auto-calculated
            $table->string('status')->default('draft'); // draft, pending, approved, rejected, published
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('carbon_stock_estimation', 12, 2)->nullable(); // calculated Ton CO2
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('region_id')
                  ->references('id')
                  ->on('regions')
                  ->onDelete('set null');

            $table->foreign('verified_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Regular indexes
            $table->index('status');
            $table->index('health_status');
            $table->index('survey_date');
            $table->index('region_id');
        });

        // Add PostGIS point geometry column (SRID 4326, POINT, 2D)
        DB::statement("SELECT AddGeometryColumn('public', 'surveys', 'location_point', 4326, 'POINT', 2)");

        // Add GIST spatial index on location_point
        DB::statement('CREATE INDEX surveys_location_point_gist ON surveys USING GIST (location_point)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the geometry column first
        DB::statement("SELECT DropGeometryColumn('public', 'surveys', 'location_point')");

        Schema::dropIfExists('surveys');
    }
};
