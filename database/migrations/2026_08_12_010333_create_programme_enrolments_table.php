<?php

use App\Enums\EnrolmentStatus;
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
        Schema::create('programme_enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained();
            $table->foreignId('prisoner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrolled_by')->constrained('users')->cascadeOnDelete();
            $table->date('enrolled_at');
            $table->string('status')->default(EnrolmentStatus::Enrolled->value);
            $table->date('completed_at')->nullable();
            $table->string('withdrawal_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programme_enrolments');
    }
};
