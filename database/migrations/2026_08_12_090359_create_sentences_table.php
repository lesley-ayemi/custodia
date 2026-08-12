<?php

use App\Enums\LegalStatus;
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
        Schema::create('sentences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prisoner_id')->constrained()->cascadeOnDelete();
            $table->string('case_number');
            $table->string('court');
            $table->string('offence');
            $table->date('sentence_start');
            $table->date('sentence_end')->nullable();
            $table->string('sentence_type');
            $table->date('parole_eligibility_date')->nullable();
            $table->string('legal_status')->default(LegalStatus::Convicted->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sentences');
    }
};
