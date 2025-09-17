<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**Route for register */
Route::post('register', 'AuthController@register');

/**Route for auth API */
Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout');
Route::get('oauth/{driver}', 'Auth\LoginController@redirectToProvider');
Route::get('oauth/{driver}/callback', 'Auth\LoginController@handleProviderCallback');
Route::post('password/email', 'Auth\ForgotPasswordController@forgot')->name('password.reset');
Route::post('password/reset', 'Auth\ForgotPasswordController@reset');

/**Route for details user API */
Route::middleware('auth:api')->middleware('cors')->group(function(){
    /**Route for register */
    Route::get('test', 'AuthController@test');
    // Route::post('logout', 'AuthController@logout');
    Route::apiResources(['contact' => 'ContactController']);
    Route::apiResources(['qualification' => 'QualificationController']);
    Route::apiResources(['upload' => 'UploadController']);
    Route::apiResources(['special_area' => 'SpecialAreaController']);
    Route::apiResources(['patient_details' => 'PatientDetailController']);
    Route::apiResources(['consultation' => 'ConsultationController']);
    Route::apiResources(['user_role' => 'UserRoleController']);
    Route::apiResources(['feedback' => 'FeedbackController']);
    Route::apiResources(['monthly_condition' => 'MonthlyConditionController']);
    Route::apiResources(['doctor_details' => 'DoctorDetailController']);
    Route::apiResources(['user_details' => 'UserDetailsController']);
    Route::apiResources(['directory' => 'DirectoryController']);
    Route::apiResources(['payouts' => 'PayoutController']);

    Route::post('/paypal', ['uses' => 'PaymentController@payWithpaypal']);
    Route::post('/payment/status',['uses' => 'PaymentController@getPaymentStatus']);
    Route::get('/payment/{id}',['uses' => 'PaymentController@show']);
    Route::get('/payments_doctor/{id}',['uses' => 'PaymentController@getDoctorPayments']);
    Route::get('/getconsultation/{id}',['uses' => 'ConsultationController@getConsultation']);
    Route::get('/consultation_details/{account}/{id}',['uses' => 'ConsultationController@consultationDetails']);
    Route::post('/accept_consultation',['uses' => 'ConsultationController@acceptConsultation']);
    Route::post('/accept_user',['uses' => 'UserDetailsController@acceptUser']);
    Route::post('/doctors_notes',['uses' => 'ConsultationController@doctorNotes']);
    Route::get('/request_form/{consultation_id}/{form_type}',['uses' => 'ConsultationController@generateRequestForm']);
    Route::get('/dashboard_condition',['uses' => 'MonthlyConditionController@getCondition']);
    Route::get('/doctor_files/{id}',['uses' => 'DoctorDetailController@showFiles']);
    Route::post('/doctor_files',['uses' => 'DoctorDetailController@storeFiles']);
    Route::get('/download_files/{id}',['uses' => 'DoctorDetailController@downloadFiles']);
    Route::get('/getPayouts',['uses' => 'PayoutController@getPayouts']);
    Route::get('/getMonthlyCondition',['uses' => 'MonthlyConditionController@getMonthlyCondition']);
    Route::get('/dashboard/{id}/{account_type}',['uses' => 'DashboardController@getData']);


    // Patient routes
    Route::apiResources(['walk_in_patient_details' => 'PatientController']);
    Route::resource('patients', WalkInPatientController::class);
    Route::post('patients/{patient}/assign-doctor', [PatientController::class, 'assignDoctor'])->name('patients.assign-doctor');
    
    // Drug routes
    Route::resource('drugs', DrugController::class);
    Route::post('drugs/{drug}/update-stock', [DrugController::class, 'updateStock'])->name('drugs.update-stock');
    
    // Prescription routes
    Route::resource('prescriptions', PrescriptionController::class);
    Route::get('patients/{patient}/prescriptions/create', [PrescriptionController::class, 'create'])->name('patient.prescriptions.create');
    
    // Sale routes
    Route::resource('sales', SaleController::class);
    Route::get('prescriptions/{prescription}/sales/create', [SaleController::class, 'create'])->name('prescription.sales.create');
    
    // Report routes
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/stock', [ReportController::class, 'stockReport'])->name('reports.stock');
    Route::get('reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');

});
