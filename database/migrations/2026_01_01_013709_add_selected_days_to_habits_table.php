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
        Schema::table('habits', function (Blueprint $table) {
            // Change frequency from enum to string to support custom days
            $table->string('frequency')->change();
            
            // Add selected_days column to store custom selected days (JSON array of day numbers: 0=Sunday, 1=Monday, etc.)
            $table->json('selected_days')->nullable()->after('frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('habits', function (Blueprint $table) {
            // Revert frequency back to enum
            $table->enum('frequency', ['everyday', 'weekdays', 'weekends'])->change();
            
            // Remove selected_days column
            $table->dropColumn('selected_days');
        });
    }
};
