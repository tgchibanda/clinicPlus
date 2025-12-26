<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\InsurancePublicController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\InsuranceSubscriptionController;
use App\Http\Controllers\ConsultationDocumentController;
use App\Http\Controllers\DrugController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Auth / public ---
Route::post('register', 'AuthController@register');
Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout');
Route::get('oauth/{driver}', 'Auth\LoginController@redirectToProvider');
Route::get('oauth/{driver}/callback', 'Auth\LoginController@handleProviderCallback');
Route::post('password/email', 'Auth\ForgotPasswordController@forgot')->name('password.reset');
Route::post('password/reset', 'Auth\ForgotPasswordController@reset');

Route::post('/public-signup', [InsurancePublicController::class, 'publicSignup']);
Route::get('/plans', [InsurancePublicController::class, 'getPlans']);
Route::post('/verify-patient', [InsurancePublicController::class, 'verifyPatient']);

// Document download - NO CORS MIDDLEWARE HERE
Route::middleware('auth:api')->get('consultation-documents/{id}/download', [ConsultationDocumentController::class, 'download']);

Route::middleware('auth:api')->middleware('cors')->group(function () {

    // --- Misc existing ---
    Route::get('test', 'AuthController@test');
    Route::apiResources(['contact' => 'ContactController']);
    Route::apiResources(['qualification' => 'QualificationController']);
    Route::apiResources(['upload' => 'UploadController']);
    Route::apiResources(['special_area' => 'SpecialAreaController']);
    Route::apiResources(['patient_details' => 'PatientDetailController']);
    Route::apiResources(['user_role' => 'UserRoleController']);
    Route::apiResources(['feedback' => 'FeedbackController']);
    Route::apiResources(['monthly_condition' => 'MonthlyConditionController']);
    Route::apiResources(['doctor_details' => 'DoctorDetailController']);
    Route::apiResources(['user_details' => 'UserDetailsController']);
    Route::post('accept_user', [UserDetailsController::class, 'acceptUser']);
    Route::post('suspend_user', [UserDetailsController::class, 'suspendUser']);
    Route::apiResources(['directory' => 'DirectoryController']);
    Route::apiResources(['payouts' => 'PayoutController']);

    Route::get('/getMonthlyCondition', ['uses' => 'MonthlyConditionController@getMonthlyCondition']);
    Route::get('/dashboard/{id}/{account_type}', ['uses' => 'DashboardController@getData']);

    // Subscription management
    Route::get('/subscriptions', [InsuranceSubscriptionController::class, 'index']);
    Route::get('/subscriptions/{id}', [InsuranceSubscriptionController::class, 'show']);
    Route::patch('/subscriptions/{id}', [InsuranceSubscriptionController::class, 'update']);
    Route::post('/subscriptions/{id}/close', [InsuranceSubscriptionController::class, 'close']);
    Route::post('/subscriptions/{id}/payments', [InsuranceSubscriptionController::class, 'addPayment']);
    Route::get('/subscriptions/{id}/payments', [InsuranceSubscriptionController::class, 'getPayments']);
    Route::get('/subscriptions/{id}/claims', [InsuranceSubscriptionController::class, 'getClaims']);
    Route::get('/subscription/verify-consultation-by-policy', [InsuranceSubscriptionController::class, 'verifyConsultationByPolicyNumber']);
    Route::get('/subscription/verify-by-policy', [InsuranceSubscriptionController::class, 'verifyByPolicyNumber']);
    
    // Reports
    Route::get('/reports/subscriptions', [InsuranceReportController::class, 'subscriptions']);
    Route::get('/reports/payments', [InsuranceReportController::class, 'payments']);
    Route::get('/reports/export', [InsuranceReportController::class, 'export']);

    // Consultations
    Route::apiResources(['consultation' => 'ConsultationController']);
    Route::get('/consultations-report', [ConsultationController::class, 'consultationsReport']);
    Route::get('walk-in-patient/{patient}/consultation_history', [ConsultationController::class, 'byPatient']);
    Route::get('/getconsultation/{id}', ['uses' => 'ConsultationController@getConsultation']);
    Route::get('/consultation_details/{account}/{id}', ['uses' => 'ConsultationController@consultationDetails']);
    Route::post('/accept_consultation', ['uses' => 'ConsultationController@acceptConsultation']);
    Route::post('/doctors_notes', ['uses' => 'ConsultationController@doctorNotes']);
    Route::get('/request_form/{consultation_id}/{form_type}', ['uses' => 'ConsultationController@generateRequestForm']);

    // Consultation Documents Routes
    Route::post('consultation-documents/upload', [ConsultationDocumentController::class, 'upload']);
    Route::get('consultations/{consultationId}/documents', [ConsultationDocumentController::class, 'index']);
    Route::delete('consultation-documents/{id}', [ConsultationDocumentController::class, 'destroy']);

    // --- Patients (walk-in) ---
    Route::apiResources(['walk_in_patient_details' => 'PatientController']);
    Route::get('walk_in_patient_details/{id}/walk-in-patient-details', [PatientController::class, 'walkInPatientDetails']);
    Route::post('walk_in_patient_details/{patient}/assign-doctor', [PatientController::class, 'assignDoctor'])
        ->name('patients.assign-doctor');

    // --- Drugs ---
    // Keep backward compatibility with drug_details endpoints
    Route::get('drug_details', [DrugController::class, 'index']);
    Route::post('drug_details', [DrugController::class, 'store']);
    Route::get('drug_details/{id}', [DrugController::class, 'show']);
    Route::put('drug_details/{id}', [DrugController::class, 'update']);
    Route::delete('drug_details/{id}', [DrugController::class, 'destroy']);
    Route::get('drug_details/{id}/drug-details', [DrugController::class, 'drugDetails']);

    // Main drugs routes
    Route::get('drugs', [DrugController::class, 'index']);
    Route::post('drugs', [DrugController::class, 'store']);
    Route::get('drugs/{id}', [DrugController::class, 'drugDetails']);
    Route::put('drugs/{id}', [DrugController::class, 'update']);
    Route::delete('drugs/{id}', [DrugController::class, 'destroy']);
    Route::post('drugs/{id}/add-stock', [DrugController::class, 'addStock']);
    Route::get('drugs/{id}/restocking-history', [DrugController::class, 'restockingHistory']);

    // --- Prescriptions ---
    Route::resource('prescriptions', PrescriptionController::class);
    Route::get('patients/{patient}/prescriptions/create', [PrescriptionController::class, 'create'])
        ->name('patient.prescriptions.create');

    // --- Sales ---
    Route::resource('sales', SaleController::class);
    Route::get('prescriptions/{prescription}/sales/create', [SaleController::class, 'create'])
        ->name('prescription.sales.create');

    // --- Reports (JSON) ---
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/stock', [ReportController::class, 'stockReport'])->name('reports.stock');
    Route::get('reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');

    // Locations & Doctors
    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::get('doctors/{doctor}/availability', [DoctorController::class, 'availability'])
        ->name('doctors.availability');
});