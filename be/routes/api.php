<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ConsultationController;

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
    Route::apiResources(['directory' => 'DirectoryController']);
    Route::apiResources(['payouts' => 'PayoutController']);

    Route::post('/paypal', ['uses' => 'PaymentController@payWithpaypal']);
    Route::post('/payment/status', ['uses' => 'PaymentController@getPaymentStatus']);
    Route::get('/payment/{id}', ['uses' => 'PaymentController@show']);
    Route::get('/payments_doctor/{id}', ['uses' => 'PaymentController@getDoctorPayments']); 
    Route::post('/accept_user', ['uses' => 'UserDetailsController@acceptUser']);   
    Route::get('/dashboard_condition', ['uses' => 'MonthlyConditionController@getCondition']);
    Route::get('/doctor_files/{id}', ['uses' => 'DoctorDetailController@showFiles']);
    Route::post('/doctor_files', ['uses' => 'DoctorDetailController@storeFiles']);
    Route::get('/download_files/{id}', ['uses' => 'DoctorDetailController@downloadFiles']);
    Route::get('/getPayouts', ['uses' => 'PayoutController@getPayouts']);
    Route::get('/getMonthlyCondition', ['uses' => 'MonthlyConditionController@getMonthlyCondition']);
    Route::get('/dashboard/{id}/{account_type}', ['uses' => 'DashboardController@getData']);

    // Consultations
    
    Route::apiResources(['consultation' => 'ConsultationController']);
    Route::get('walk-in-patient/{patient}/consultation_history', [ConsultationController::class, 'byPatient']);
    Route::get('/getconsultation/{id}', ['uses' => 'ConsultationController@getConsultation']);
    Route::get('/consultation_details/{account}/{id}', ['uses' => 'ConsultationController@consultationDetails']);
    Route::post('/accept_consultation', ['uses' => 'ConsultationController@acceptConsultation']);
    Route::post('/doctors_notes', ['uses' => 'ConsultationController@doctorNotes']);
    Route::get('/request_form/{consultation_id}/{form_type}', ['uses' => 'ConsultationController@generateRequestForm']);

    // --- Patients (walk-in) ---
    Route::apiResources(['walk_in_patient_details' => 'PatientController']);
    Route::get('walk_in_patient_details/{id}/walk-in-patient-details', [PatientController::class, 'walkInPatientDetails']);
    Route::post('walk_in_patient_details/{patient}/assign-doctor', [PatientController::class, 'assignDoctor'])
        ->name('patients.assign-doctor');

    // --- Drugs ---
    Route::apiResources(['drug_details' => 'DrugController']);
    Route::get('drug_details/{id}/drug-details', [DrugController::class, 'drugDetails']);
    Route::resource('drugs', DrugController::class);
    Route::post('drugs/{drug}/update-stock', [DrugController::class, 'updateStock'])->name('drugs.update-stock');

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

    // ======================================================
    // NEW: Location + Doctor availability endpoints (for booking)
    // ======================================================

    // 1) Locations list
    // GET /api/locations  -> { data: [ {id, name, ...}, ... ] }
    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');

    // 2) Doctors by location (supports ?location_id=)
    // GET /api/doctors?location_id=ID -> { data: [ {id, name, location:{}, is_super_doctor}, ... ] }
    Route::get('doctors', [DoctorController::class, 'index'])->name('doctors.index');

    // 3) Doctor availability by date + location
    // GET /api/doctors/{doctor}/availability?date=YYYY-MM-DD&location_id=ID
    // -> { data: { booked_slots:[], doctor_is_super:bool, locked_location_id:int|null, work_hours:{start,end} } }
    Route::get('doctors/{doctor}/availability', [DoctorController::class, 'availability'])
        ->name('doctors.availability');

    // (You already have) 4) Create consultation booking
    // POST /api/consultation  (handled by ConsultationController@store)
});
