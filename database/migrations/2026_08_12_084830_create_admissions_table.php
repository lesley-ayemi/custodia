<?php

use App\Enums\AdmissionStatus;
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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prisoner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admitted_by')->constrained('users')->cascadeOnDelete();
            $table->date('admission_date');
            $table->string('admission_reason');
            $table->string('legal_authority_reference')->nullable();
            $table->text('initial_assessment_notes')->nullable();
            $table->string('security_classification')->nullable();
            $table->string('status')->default(AdmissionStatus::Draft->value);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
