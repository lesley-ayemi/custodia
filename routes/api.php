<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\CellController;
use App\Http\Controllers\Api\CourtCaseController;
use App\Http\Controllers\Api\CourtHearingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HousingAssignmentController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\LegalRepresentativeController;
use App\Http\Controllers\Api\MedicalAlertController;
use App\Http\Controllers\Api\MedicalAppointmentController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\PrisonerController;
use App\Http\Controllers\Api\ProgrammeAttendanceController;
use App\Http\Controllers\Api\ProgrammeController;
use App\Http\Controllers\Api\ProgrammeEnrolmentController;
use App\Http\Controllers\Api\PropertyItemController;
use App\Http\Controllers\Api\ReleaseReviewController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\VisitorController;
use App\Http\Controllers\Api\VisitRequestController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('prisoners', PrisonerController::class)->except(['destroy']);
    Route::post('/prisoners/{prisoner}/archive', [PrisonerController::class, 'archive']);
    Route::get('/prisoners/{prisoner}/housing-history', [HousingAssignmentController::class, 'history']);

    Route::apiResource('blocks', BlockController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('cells', CellController::class)->only(['store', 'update', 'destroy']);
    Route::post('/housing-assignments', [HousingAssignmentController::class, 'store']);

    Route::apiResource('incidents', IncidentController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('/incidents/{incident}/review', [IncidentController::class, 'markUnderReview']);
    Route::post('/incidents/{incident}/resolve', [IncidentController::class, 'resolve']);

    Route::get('/audit-logs', [AuditLogController::class, 'index']);

    Route::apiResource('users', UserController::class);

    Route::get('/court-hearings/upcoming', [CourtHearingController::class, 'upcoming']);
    Route::get('/prisoners/{prisoner}/court-cases', [CourtCaseController::class, 'indexForPrisoner']);
    Route::post('/prisoners/{prisoner}/court-cases', [CourtCaseController::class, 'store']);
    Route::get('/court-cases/{courtCase}', [CourtCaseController::class, 'show']);
    Route::post('/court-cases/{courtCase}/hearings', [CourtHearingController::class, 'store']);

    Route::get('/legal-representatives', [LegalRepresentativeController::class, 'index']);
    Route::post('/legal-representatives', [LegalRepresentativeController::class, 'store']);

    Route::get('/prisoners/{prisoner}/property', [PropertyItemController::class, 'indexForPrisoner']);
    Route::post('/prisoners/{prisoner}/property', [PropertyItemController::class, 'store']);
    Route::post('/property-items/{propertyItem}/release', [PropertyItemController::class, 'release']);

    Route::apiResource('programmes', ProgrammeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/prisoners/{prisoner}/programme-enrolments', [ProgrammeEnrolmentController::class, 'indexForPrisoner']);
    Route::post('/prisoners/{prisoner}/programme-enrolments', [ProgrammeEnrolmentController::class, 'store']);
    Route::post('/programme-enrolments/{programmeEnrolment}/complete', [ProgrammeEnrolmentController::class, 'complete']);
    Route::post('/programme-enrolments/{programmeEnrolment}/withdraw', [ProgrammeEnrolmentController::class, 'withdraw']);
    Route::post('/programme-enrolments/{programmeEnrolment}/attendance', [ProgrammeAttendanceController::class, 'store']);

    Route::get('/release-reviews', [ReleaseReviewController::class, 'index']);
    Route::get('/prisoners/{prisoner}/release-reviews', [ReleaseReviewController::class, 'indexForPrisoner']);
    Route::post('/prisoners/{prisoner}/release-reviews', [ReleaseReviewController::class, 'store']);
    Route::post('/release-reviews/{releaseReview}/legal-verification', [ReleaseReviewController::class, 'recordLegalVerification']);
    Route::post('/release-reviews/{releaseReview}/sentence-verification', [ReleaseReviewController::class, 'recordSentenceVerification']);
    Route::post('/release-reviews/{releaseReview}/property-verification', [ReleaseReviewController::class, 'recordPropertyVerification']);
    Route::post('/release-reviews/{releaseReview}/documentation', [ReleaseReviewController::class, 'recordDocumentation']);
    Route::post('/release-reviews/{releaseReview}/supervisor-approval', [ReleaseReviewController::class, 'approveBySupervisor']);
    Route::post('/release-reviews/{releaseReview}/cancel', [ReleaseReviewController::class, 'cancel']);

    Route::get('/prisoners/{prisoner}/medical-records', [MedicalRecordController::class, 'indexForPrisoner']);
    Route::post('/prisoners/{prisoner}/medical-records', [MedicalRecordController::class, 'store']);

    Route::get('/medical-appointments/upcoming', [MedicalAppointmentController::class, 'upcoming']);
    Route::get('/prisoners/{prisoner}/medical-appointments', [MedicalAppointmentController::class, 'indexForPrisoner']);
    Route::post('/prisoners/{prisoner}/medical-appointments', [MedicalAppointmentController::class, 'store']);
    Route::post('/medical-appointments/{medicalAppointment}/complete', [MedicalAppointmentController::class, 'complete']);
    Route::post('/medical-appointments/{medicalAppointment}/cancel', [MedicalAppointmentController::class, 'cancel']);

    Route::get('/prisoners/{prisoner}/prescriptions', [PrescriptionController::class, 'indexForPrisoner']);
    Route::post('/prisoners/{prisoner}/prescriptions', [PrescriptionController::class, 'store']);
    Route::post('/prescriptions/{prescription}/discontinue', [PrescriptionController::class, 'discontinue']);

    Route::get('/prisoners/{prisoner}/medical-alerts', [MedicalAlertController::class, 'indexForPrisoner']);
    Route::post('/prisoners/{prisoner}/medical-alerts', [MedicalAlertController::class, 'store']);
    Route::put('/medical-alerts/{medicalAlert}', [MedicalAlertController::class, 'update']);
    Route::post('/medical-alerts/{medicalAlert}/resolve', [MedicalAlertController::class, 'resolve']);

    Route::get('/visitors', [VisitorController::class, 'index']);
    Route::post('/visitors', [VisitorController::class, 'store']);

    Route::get('/prisoners/{prisoner}/visit-requests', [VisitRequestController::class, 'indexForPrisoner']);
    Route::post('/visit-requests', [VisitRequestController::class, 'store']);
    Route::post('/visit-requests/{visitRequest}/approve', [VisitRequestController::class, 'approve']);
    Route::post('/visit-requests/{visitRequest}/reject', [VisitRequestController::class, 'reject']);

    Route::get('/visits/upcoming', [VisitController::class, 'upcoming']);
    Route::get('/prisoners/{prisoner}/visits', [VisitController::class, 'indexForPrisoner']);
    Route::post('/visits/{visit}/check-in', [VisitController::class, 'checkIn']);
    Route::post('/visits/{visit}/check-out', [VisitController::class, 'checkOut']);
    Route::post('/visits/{visit}/cancel', [VisitController::class, 'cancel']);
});
