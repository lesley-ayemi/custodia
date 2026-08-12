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
        Schema::create('property_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prisoner_id')->constrained()->cascadeOnDelete();
            $table->string('property_number');
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('storage_location');
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('received_at');
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index('property_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_items');
    }
};
