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
        Schema::table('property_items', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('storage_location');
            $table->string('released_to')->nullable()->after('released_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_items', function (Blueprint $table) {
            $table->dropColumn(['notes', 'released_to']);
        });
    }
};
