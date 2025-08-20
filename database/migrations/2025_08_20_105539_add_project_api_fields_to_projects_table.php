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
        Schema::table('projects', function (Blueprint $table) {
            // Add missing fields for API functionality
            $table->decimal('budget', 10, 2)->nullable()->after('location_url');
            $table->date('deadline')->nullable()->after('budget');
            $table->string('manager_email')->nullable()->after('deadline');
            
            // Drop the old project_manager_email column if it exists
            if (Schema::hasColumn('projects', 'project_manager_email')) {
                $table->dropColumn('project_manager_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['budget', 'deadline', 'manager_email']);
            $table->string('project_manager_email')->nullable();
        });
    }
};
