<?php

use App\Enums\VisitRequestStatus;
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
        Schema::create('visit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prisoner_id')->constrained()->cascadeOnDelete();
            $table->string('relationship');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->date('requested_visit_date');
            $table->string('status')->default(VisitRequestStatus::Pending->value);
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_requests');
    }
};
