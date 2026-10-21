<?php

use App\Http\Controllers\Admin\DoctorScheduleController;
use App\Http\Controllers\Admin\InstitutionalReportController;
use App\Http\Controllers\Admin\SpecialtyController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PatientRegistrationController;
use App\Http\Controllers\MedicalConsultationController;
use App\Http\Controllers\NursingIntakeController;
use App\Http\Controllers\PatientDirectoryController;
use App\Http\Controllers\PatientHistoryController;
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

    Route::middleware('role:patient')->group(function (): void {
        Route::get('/citas/disponibilidad', [AppointmentController::class, 'availability'])->name('appointments.availability');
        Route::get('/mis-citas', [AppointmentController::class, 'patientIndex'])->name('appointments.mine');
        Route::post('/citas', [AppointmentController::class, 'storeForPatient'])->name('appointments.store');
        Route::post('/citas/{appointment}/confirmar', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
        Route::post('/citas/{appointment}/cancelar', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    });

    Route::get('/agenda-medica', [AppointmentController::class, 'doctorAgenda'])
        ->middleware('role:doctor')
        ->name('appointments.doctor-agenda');

    Route::middleware('role:doctor')->group(function (): void {
        Route::get('/medico/pacientes/{patient}/historial', [PatientHistoryController::class, 'show'])
            ->name('medical-records.history');
        Route::get('/agenda-medica/{appointment}/consulta', [MedicalConsultationController::class, 'create'])
            ->name('medical-consultations.create');
        Route::post('/agenda-medica/{appointment}/consulta', [MedicalConsultationController::class, 'store'])
            ->name('medical-consultations.store');
    });

    Route::middleware('role:nurse')->group(function (): void {
        Route::get('/enfermeria/recepcion', [NursingIntakeController::class, 'index'])->name('nursing.intakes.index');
        Route::get('/enfermeria/citas/{appointment}', [NursingIntakeController::class, 'edit'])->name('nursing.intakes.edit');
        Route::post('/enfermeria/citas/{appointment}', [NursingIntakeController::class, 'store'])->name('nursing.intakes.store');
        Route::post('/enfermeria/registros/{nursingIntake}/rectificaciones', [NursingIntakeController::class, 'amend'])
            ->name('nursing.intakes.amendments.store');
    });

    Route::get('/agenda-medica/{appointment}/recepcion', [NursingIntakeController::class, 'doctorShow'])
        ->middleware('role:doctor')
        ->name('nursing.intakes.doctor-show');

    Route::resource('/admin/usuarios', UserController::class)
        ->names('admin.users')
        ->except(['show'])
        ->middleware('role:admin');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/admin/reportes', [InstitutionalReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/admin/reportes/exportar', [InstitutionalReportController::class, 'export'])->name('admin.reports.export');

        Route::resource('/admin/personal', StaffController::class)
            ->names('admin.staff')
            ->parameters(['personal' => 'staff'])
            ->except(['show']);
        Route::resource('/admin/especialidades', SpecialtyController::class)
            ->names('admin.specialties')
            ->parameters(['especialidades' => 'specialty'])
            ->except(['show']);
        Route::get('/admin/personal/{staff}/horarios', [DoctorScheduleController::class, 'edit'])
            ->name('admin.staff.schedules.edit');
        Route::put('/admin/personal/{staff}/horarios', [DoctorScheduleController::class, 'update'])
            ->name('admin.staff.schedules.update');
        Route::get('/admin/citas/crear', [AppointmentController::class, 'adminCreate'])->name('admin.appointments.create');
        Route::post('/admin/citas', [AppointmentController::class, 'storeForPatientByAdmin'])->name('admin.appointments.store');
    });
});
