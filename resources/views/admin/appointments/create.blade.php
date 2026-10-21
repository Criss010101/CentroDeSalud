@extends('layouts.app')

@section('title', 'Agendar cita presencial')

@section('content')
<section class="mx-auto max-w-4xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administración</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Agendar cita presencial</h1>
    <p class="mt-2 text-slate-600">Selecciona una fecha y médico para cargar los horarios libres.</p>

    <form method="GET" action="{{ route('admin.appointments.create') }}" class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <div>
            <label for="date" class="mb-2 block text-sm font-medium">Fecha</label>
            <input id="date" name="date" type="date" min="{{ today()->toDateString() }}" value="{{ $date->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3">
        </div>
        <div>
            <label for="staff_profile_id" class="mb-2 block text-sm font-medium">Médico</label>
            <select id="staff_profile_id" name="staff_profile_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                <option value="">Selecciona un médico</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected((string) request('staff_profile_id') === (string) $doctor->id)>{{ $doctor->user->name }} · {{ $doctor->specialties->pluck('name')->join(', ') }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Ver horarios</button>
    </form>

    @if ($selectedDoctor)
        <form method="POST" action="{{ route('admin.appointments.store') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @csrf
            <input type="hidden" name="appointment_date" value="{{ $date->toDateString() }}">
            <input type="hidden" name="staff_profile_id" value="{{ $selectedDoctor->id }}">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="patient_id" class="mb-2 block text-sm font-medium">Paciente *</label>
                    <select id="patient_id" name="patient_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                        <option value="">Selecciona un paciente</option>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}" @selected((string) old('patient_id') === (string) $patient->id)>{{ $patient->name }} · {{ $patient->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_time" class="mb-2 block text-sm font-medium">Horario disponible *</label>
                    <select id="start_time" name="start_time" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                        <option value="">Selecciona un horario</option>
                        @foreach ($availableTimes as $time)
                            <option value="{{ $time }}" @selected(old('start_time') === $time)>{{ $time }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="reason" class="mb-2 block text-sm font-medium">Motivo de consulta (opcional)</label>
                    <input id="reason" name="reason" value="{{ old('reason') }}" maxlength="500" class="w-full rounded-xl border border-slate-300 px-4 py-3">
                </div>
            </div>
            <button type="submit" class="mt-6 rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">Registrar y confirmar cita</button>
        </form>
    @elseif ($doctors->isEmpty())
        <p class="mt-6 rounded-2xl bg-white p-6 text-slate-600">No hay médicos registrados con acceso al sistema.</p>
    @endif
</section>
@endsection
