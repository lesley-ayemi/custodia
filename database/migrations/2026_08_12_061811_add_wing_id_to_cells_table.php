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
        Schema::table('cells', function (Blueprint $table) {
            $table->foreignId('wing_id')->nullable()->after('block_id')->constrained()->cascadeOnDelete();
        });

        // Every existing block gets one default wing, and its cells move under it -
        // preserves current housing data instead of losing it.
        foreach (DB::table('blocks')->orderBy('id')->get() as $block) {
            $wingId = DB::table('wings')->insertGetId([
                'block_id' => $block->id,
                'name' => 'Wing 1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cells')->where('block_id', $block->id)->update(['wing_id' => $wingId]);
        }

        DB::statement('ALTER TABLE cells ALTER COLUMN wing_id SET NOT NULL');

        Schema::table('cells', function (Blueprint $table) {
            $table->dropConstrainedForeignId('block_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cells', function (Blueprint $table) {
            $table->foreignId('block_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        DB::statement('UPDATE cells SET block_id = wings.block_id FROM wings WHERE cells.wing_id = wings.id');

        DB::statement('ALTER TABLE cells ALTER COLUMN block_id SET NOT NULL');

        Schema::table('cells', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wing_id');
        });
    }
};
