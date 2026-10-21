@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
@php
    $user = auth()->user();
    $roleName = $user->role?->name ?? 'Usuario';
    $isAdmin = $user->hasRole('admin');
    $isDoctor = $user->hasRole('doctor');
    $isNurse = $user->hasRole('nurse');
    $isPatient = $user->hasRole('patient');
@endphp

<section>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700">Centro de Salud San Juan</p>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                @if ($isAdmin)
                    Panel de administración
                @elseif ($isDoctor)
                    Panel médico
                @elseif ($isNurse)
                    Panel de enfermería
                @elseif ($isPatient)
                    Panel del paciente
                @else
                    Panel de control
                @endif
            </h1>
            <p class="mt-1 text-sm text-slate-500">Bienvenido al sistema del Centro de Salud San Juan.</p>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-600 shadow-sm">
            <svg viewBox="0 0 24 24" class="size-4 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    @if ($isAdmin)
        <div class="grid gap-4 sm:grid-cols-2">
            <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-700">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm5-3h5m-2.5-2.5v5"/></svg>
                </span>
                <div>
                    <p class="text-sm font-medium text-slate-500">Pacientes registrados</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($patientCount) }}</p>
                </div>
            </article>
            <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-700">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm5-2h5m-2.5-2.5v5"/></svg>
                </span>
                <div>
                    <p class="text-sm font-medium text-slate-500">Cuentas de usuario</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($userCount) }}</p>
                </div>
            </article>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <a href="{{ route('patients.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
                <span class="grid size-11 place-items-center rounded-xl bg-teal-50 text-teal-700">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4m-5-8v6m-3-3h6"/></svg>
                </span>
                <h2 class="mt-4 text-lg font-bold text-slate-900">Directorio de pacientes</h2>
                <p class="mt-1 text-sm text-slate-500">Busca y consulta las fichas personales registradas.</p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Ver pacientes <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
                <span class="grid size-11 place-items-center rounded-xl bg-violet-50 text-violet-700">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm5-2h5m-2.5-2.5v5"/></svg>
                </span>
                <h2 class="mt-4 text-lg font-bold text-slate-900">Usuarios y roles</h2>
                <p class="mt-1 text-sm text-slate-500">Administra las cuentas y los roles existentes.</p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Administrar usuarios <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
            </a>
            <a href="{{ route('admin.staff.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
                <h2 class="text-lg font-bold text-slate-900">Personal médico y administrativo</h2>
                <p class="mt-1 text-sm text-slate-500">Registra cuentas laborales y asigna especialidades.</p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Administrar personal <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
            </a>
            <a href="{{ route('admin.specialties.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
                <h2 class="text-lg font-bold text-slate-900">Especialidades</h2>
                <p class="mt-1 text-sm text-slate-500">Mantén el catálogo de atención médica del centro.</p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Administrar especialidades <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
            </a>
            <a href="{{ route('admin.appointments.create') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
                <h2 class="text-lg font-bold text-slate-900">Agendar cita presencial</h2>
                <p class="mt-1 text-sm text-slate-500">Registra citas para pacientes atendidos en ventanilla.</p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Crear cita <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
                <h2 class="text-lg font-bold text-slate-900">Reportes institucionales</h2>
                <p class="mt-1 text-sm text-slate-500">Consulta estadísticas mensuales de pacientes, citas y atención médica.</p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Ver reportes <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
            </a>
        </div>
    @elseif ($isDoctor)
        <div class="grid gap-4 sm:grid-cols-2">
            <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-700">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm5-3h5m-2.5-2.5v5"/></svg>
                </span>
                <div>
                    <p class="text-sm font-medium text-slate-500">Pacientes registrados</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($patientCount) }}</p>
                </div>
            </article>
        </div>

        <a href="{{ route('patients.index') }}" class="group mt-6 block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
            <span class="grid size-11 place-items-center rounded-xl bg-teal-50 text-teal-700">
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4m-5-8v6m-3-3h6"/></svg>
            </span>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Directorio de pacientes</h2>
            <p class="mt-1 text-sm text-slate-500">Busca pacientes por nombre, correo o carnet y consulta sus datos personales.</p>
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Buscar pacientes <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
        </a>
        <a href="{{ route('appointments.doctor-agenda') }}" class="group mt-4 block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
            <h2 class="text-lg font-bold text-slate-900">Agenda médica</h2>
            <p class="mt-1 text-sm text-slate-500">Consulta las citas programadas para el día.</p>
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Ver agenda <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
        </a>
    @elseif ($isPatient)
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(16rem,0.8fr)]">
            <a href="{{ route('patients.profile.edit') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
                <span class="grid size-12 place-items-center rounded-xl bg-teal-50 text-teal-700">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 9v-2a7 7 0 0 0-14 0v2m11-8 2 2 4-4"/></svg>
                </span>
                <p class="mt-4 text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Datos personales</p>
                <h2 class="mt-1 text-xl font-bold text-slate-900">Mi ficha personal</h2>
                <p class="mt-2 text-sm text-slate-500">Consulta o actualiza tus datos personales y de contacto.</p>
                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Ver mi ficha <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
            </a>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Cuenta</p>
                <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $user->name }}</h2>
                <p class="mt-1 break-all text-sm text-slate-500">{{ $user->email }}</p>
                <span class="mt-4 inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800">{{ $roleName }}</span>
            </article>
        </div>
    @elseif ($isNurse)
        <a href="{{ route('nursing.intakes.index') }}" class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-6">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Atención inicial</p>
            <h2 class="mt-2 text-xl font-bold text-slate-900">Recepción y signos vitales</h2>
            <p class="mt-2 text-sm text-slate-500">Consulta las citas del día y registra las mediciones iniciales para el médico responsable.</p>
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-800">Abrir recepción <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
        </a>
    @else
        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-6 text-sky-900">Tu cuenta está habilitada con acceso limitado. Comunícate con el administrador si necesitas permisos adicionales.</div>
    @endif
</section>
@endsection
