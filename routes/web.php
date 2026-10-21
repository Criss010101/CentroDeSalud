<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PatientRegistrationController;
use App\Http\Controllers\PatientDirectoryController;
use App\Http\Controllers\PatientProfileController;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/ingresar');

Route::middleware('guest')->group(function (): void {
    Route::get('/ingresar', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/ingresar', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/registro', [PatientRegistrationController::class, 'create'])->name('register');
    Route::post('/registro', [PatientRegistrationController::class, 'store'])
        ->middleware('throttle:registration')
        ->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/salir', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/inicio', function () {
        return view('dashboard', [
            'patientCount' => PatientProfile::query()->count(),
            'userCount' => User::query()->count(),
        ]);
    })->name('dashboard');

    Route::middleware('role:patient')->group(function (): void {
        Route::get('/mi-ficha', [PatientProfileController::class, 'edit'])->name('patients.profile.edit');
        Route::put('/mi-ficha', [PatientProfileController::class, 'update'])->name('patients.profile.update');
    });

    Route::middleware('role:admin,doctor')->group(function (): void {
        Route::get('/pacientes', [PatientDirectoryController::class, 'index'])->name('patients.index');
    });

    Route::resource('/admin/usuarios', UserController::class)
        ->names('admin.users')
        ->except(['show'])
        ->middleware('role:admin');
});
