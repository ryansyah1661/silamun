<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create regions table for provinces and kabupaten with PostGIS geometry.
     */
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'provinsi', 'kabupaten'
            $table->string('code')->unique(); // region code like '31' for DKI Jakarta
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->decimal('area_hectares', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('regions')
                  ->onDelete('set null');
        });

        // Add PostGIS geometry column (SRID 4326, MULTIPOLYGON, 2D)
        DB::statement("SELECT AddGeometryColumn('public', 'regions', 'geometry', 4326, 'MULTIPOLYGON', 2)");

        // Add GIST spatial index on geometry column
        DB::statement('CREATE INDEX regions_geometry_gist ON regions USING GIST (geometry)');

        // Add foreign key from users.assigned_region_id to regions.id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('assigned_region_id')
                  ->references('id')
                  ->on('regions')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key from users table first
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_region_id']);
        });

        // Drop the geometry column
        DB::statement("SELECT DropGeometryColumn('public', 'regions', 'geometry')");

        Schema::dropIfExists('regions');
    }
};
