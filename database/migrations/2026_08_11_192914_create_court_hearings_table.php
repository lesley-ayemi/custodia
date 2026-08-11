<?php

use App\Enums\HearingStatus;
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
        Schema::create('court_hearings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_case_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->timestamp('scheduled_at');
            $table->string('location');
            $table->string('status')->default(HearingStatus::Scheduled->value);
            $table->text('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_hearings');
    }
};
