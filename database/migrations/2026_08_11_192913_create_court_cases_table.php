<?php

use App\Enums\CourtCaseStatus;
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
        Schema::create('court_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('prisoner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('legal_representative_id')->nullable()->constrained()->nullOnDelete();
            $table->string('court_name');
            $table->string('charge');
            $table->string('status')->default(CourtCaseStatus::Open->value);
            $table->date('opened_at');
            $table->date('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_cases');
    }
};
