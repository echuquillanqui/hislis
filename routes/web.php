<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AttentionController;
use App\Http\Controllers\TriageController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\SpecialtyLabController;
use App\Http\Controllers\LabExamController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\VoucherController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {

    Route::resource('settings', SettingController::class)->only(['index', 'update']);
    Route::prefix('admin')->middleware('route.permission')->group(function () {
        Route::resource('areas', AreaController::class);
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('patients', PatientController::class);
        Route::get('monitor', [AttentionController::class, 'index'])->name('attentions.index');
        Route::post('monitor', [AttentionController::class, 'store'])->name('attentions.store');
        Route::get('monitor/{item}/print', [AttentionController::class, 'print'])->name('attentions.print');
        Route::resource('triage', TriageController::class);
        Route::post('templates/{template}/publish', [TemplateController::class, 'publish'])->name('templates.publish');
        Route::get('templates/{template}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
        Route::resource('templates', TemplateController::class);
        Route::resource('specialty_labs', SpecialtyLabController::class)->names('specialty_labs');

        
        // Rutas para Exámenes de Laboratorio
        Route::resource('lab_exams', LabExamController::class)->names('lab_exams');
        Route::resource('services', ServiceController::class);
        Route::resource('bundles', BundleController::class)->names('bundles');

        Route::get('vouchers/search-patients', [VoucherController::class, 'searchPatients'])->name('vouchers.search-patients');
        Route::delete('vouchers/item/{item}', [VoucherController::class, 'destroyItem'])->name('vouchers.destroy-item');
        Route::get('vouchers/{voucher}/print', [VoucherController::class, 'printTicket'])->name('vouchers.print');

        Route::resource('vouchers', VoucherController::class);


    });
        
});
