@extends('layouts.app')

@section('title', 'Registrar consulta médica')

@section('content')
<section class="mx-auto max-w-4xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Atención médica</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Registrar consulta</h1>

    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
        <dl class="grid gap-4 text-sm sm:grid-cols-3">
            <div>
                <dt class="font-semibold text-slate-500">Paciente</dt>
                <dd class="mt-1 font-semibold">{{ $appointment->patient->name }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-500">Fecha de cita</dt>
                <dd class="mt-1">{{ $appointment->appointment_date->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-500">Hora</dt>
                <dd class="mt-1">{{ substr($appointment->start_time, 0, 5) }}</dd>
            </div>
        </dl>
        @if ($appointment->nursingIntake)
            <p class="mt-4 text-sm text-slate-600">La recepción de enfermería está disponible para consulta desde el historial clínico. Revísala como dato inicial, no como diagnóstico médico.</p>
        @endif
    </div>

    @if ($errors->any())
        <div role="alert" class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Revisa la información de la consulta:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('medical-consultations.store', $appointment) }}" class="mt-5 grid gap-5 rounded-2xl border border-slate-200 bg-white p-5 sm:p-8">
        @csrf
        <p class="text-sm text-slate-600">Registra la valoración profesional. No se generan diagnósticos ni recomendaciones automáticas.</p>
        <div>
            <label for="reason" class="mb-2 block text-sm font-semibold text-slate-700">Motivo de consulta *</label>
            <textarea id="reason" name="reason" rows="3" maxlength="5000" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('reason', $suggestedReason) }}</textarea>
            @error('reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="observations" class="mb-2 block text-sm font-semibold text-slate-700">Observaciones médicas *</label>
            <textarea id="observations" name="observations" rows="4" maxlength="10000" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('observations') }}</textarea>
            @error('observations')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="diagnosis" class="mb-2 block text-sm font-semibold text-slate-700">Diagnóstico *</label>
            <textarea id="diagnosis" name="diagnosis" rows="3" maxlength="5000" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('diagnosis') }}</textarea>
            @error('diagnosis')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="treatment" class="mb-2 block text-sm font-semibold text-slate-700">Tratamiento *</label>
            <textarea id="treatment" name="treatment" rows="3" maxlength="10000" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('treatment') }}</textarea>
            @error('treatment')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="recommendations" class="mb-2 block text-sm font-semibold text-slate-700">Recomendaciones *</label>
            <textarea id="recommendations" name="recommendations" rows="3" maxlength="10000" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('recommendations') }}</textarea>
            @error('recommendations')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Guardar consulta</button>
            <a href="{{ route('appointments.doctor-agenda', ['date' => $appointment->appointment_date->toDateString()]) }}" class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
        </div>
    </form>
</section>
@endsection
