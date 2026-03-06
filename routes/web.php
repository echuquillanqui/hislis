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
    Route::prefix('admin')->group(function () {
        Route::resource('areas', AreaController::class);
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('patients', PatientController::class);
        Route::get('monitor', [AttentionController::class, 'index'])->name('attentions.index');
        Route::resource('triage', TriageController::class);
        Route::resource('templates', TemplateController::class);
        Route::get('templates/{template}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
        Route::resource('specialty_labs', SpecialtyLabController::class)->names('specialty_labs');

        
        // Rutas para Exámenes de Laboratorio
        Route::resource('lab_exams', LabExamController::class)->names('lab_exams');
        Route::resource('services', ServiceController::class);
        Route::resource('bundles', BundleController::class)->names('bundles');

         Route::get('vouchers/{voucher}/print', [VoucherController::class, 'printTicket'])
        ->name('vouchers.print');

        Route::delete('/vouchers/item/{item}', [VoucherController::class, 'destroyItem'])->name('vouchers.destroy-item');

        Route::resource('vouchers', VoucherController::class);
        Route::get('admin/vouchers/search-patients', [VoucherController::class, 'searchPatients']);
        Route::delete('admin/vouchers/item/{id}', [VoucherController::class, 'destroyItem']);

    });
        
});
