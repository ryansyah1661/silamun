<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add role-based access control and profile fields to users table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('surveyor')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->string('institution')->nullable()->after('phone');
            $table->unsignedBigInteger('assigned_region_id')->nullable()->after('institution');
            $table->boolean('is_active')->default(true)->after('assigned_region_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'institution',
                'assigned_region_id',
                'is_active',
            ]);
        });
    }
};
