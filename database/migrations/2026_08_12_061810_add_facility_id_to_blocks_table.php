<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->foreignId('facility_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        $facilityId = DB::table('facilities')->insertGetId([
            'name' => 'HMP Custodia',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('blocks')->update(['facility_id' => $facilityId]);

        DB::statement('ALTER TABLE blocks ALTER COLUMN facility_id SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('facility_id');
        });
    }
};
