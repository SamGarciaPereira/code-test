<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\SiteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// website 
Route::get('', [ SiteController::class, 'getIndex' ])->name('index');

// rotas de visitantes (sem login)
Route::middleware('guest')->group(function () {
    Route::get('login', [ AuthController::class, 'getLogin' ])->name('login');
    Route::post('login', [ AuthController::class, 'postLogin' ])->name('login.post');
    Route::get('cadastro', [ AuthController::class, 'getRegister' ])->name('register');
    Route::post('cadastro', [ AuthController::class, 'postRegister' ])->name('register.post');
});

// rotas autenticadas 
Route::middleware('auth')->group(function () {
    
    // logout
    Route::post('logout', [ AuthController::class, 'getLogout' ])->name('logout');

    // cliente
    Route::get('cliente', [ SiteController::class, 'getClient' ])->name('client');
    
    Route::get('editar-paciente/{patient_id?}', [ SiteController::class, 'getEditPatient' ])->name('client.edit-patient');
    Route::post('editar-paciente/{patient_id?}', [ SiteController::class, 'postEditPatient' ])->name('client.edit-patient.post');
    
    Route::get('remover-paciente/{patient_id}', [ SiteController::class, 'getRemovePatient' ])->name('client.remove-patient');
    
    Route::get('agendar-consulta', [ SiteController::class, 'getCreateAppointment' ])->name('client.create-appointment');
    Route::post('agendar-consulta', [ SiteController::class, 'postCreateAppointment' ])->name('client.create-appointment.post');
    Route::get('consulta/{appointment_id}', [ SiteController::class, 'getAppointment' ])->name('client.view-appointment');
    Route::get('horarios-disponiveis', [ SiteController::class, 'getAvailableTimes' ])->name('api.available-times');

    // veterinário
    Route::middleware('auth:vet')->group(function () {
        Route::get('vet', [ SiteController::class, 'getVet' ])->name('vet');
        Route::get('editar-consulta/{appointment_id}', [ SiteController::class, 'getEditAppointment' ])->name('vet.edit-appointment');
        Route::post('editar-consulta/{appointment_id}', [ SiteController::class, 'postEditAppointment' ])->name('vet.edit-appointment.post');
    });

});