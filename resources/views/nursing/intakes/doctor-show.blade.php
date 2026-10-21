@extends('layouts.app')

@section('title', 'Datos iniciales del paciente')

@section('content')
<section class="mx-auto max-w-4xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Atención médica</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Datos iniciales de recepción</h1>
    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
        <dl class="grid gap-4 sm:grid-cols-3">
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paciente</dt><dd class="mt-1 font-semibold">{{ $appointment->patient->name }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha y hora de cita</dt><dd class="mt-1 font-semibold">{{ $appointment->appointment_date->format('d/m/Y') }} · {{ substr($appointment->start_time, 0, 5) }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registrado por</dt><dd class="mt-1 font-semibold">{{ $nursingIntake->nurse->name }}</dd></div>
        </dl>
        <p class="mt-4 text-sm text-slate-500">Mediciones registradas el {{ $nursingIntake->measured_at->format('d/m/Y H:i') }}. Estos datos son de recepción y no constituyen un diagnóstico médico.</p>
    </div>

    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 sm:p-8">
        <h2 class="text-xl font-bold text-slate-900">Versión vigente</h2>
        @if ($latestAmendment)
            <p class="mt-2 text-sm text-slate-600">Rectificada por {{ $latestAmendment->author->name }} el {{ $latestAmendment->created_at->format('d/m/Y H:i') }}. Motivo: {{ $latestAmendment->reason }}</p>
        @endif
        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2"><dt class="text-sm font-semibold text-slate-500">Motivo referido</dt><dd class="mt-1 whitespace-pre-wrap">{{ $measurements['reason'] }}</dd></div>
            <div><dt class="text-sm font-semibold text-slate-500">Presión arterial</dt><dd class="mt-1">{{ $measurements['blood_pressure_systolic'] !== null ? $measurements['blood_pressure_systolic'].' / '.$measurements['blood_pressure_diastolic'].' mmHg' : 'No registrada' }}</dd></div>
            <div><dt class="text-sm font-semibold text-slate-500">Frecuencia cardíaca</dt><dd class="mt-1">{{ $measurements['heart_rate'] !== null ? $measurements['heart_rate'].' latidos/min' : 'No registrada' }}</dd></div>
            <div><dt class="text-sm font-semibold text-slate-500">Temperatura</dt><dd class="mt-1">{{ $measurements['temperature_celsius'] !== null ? $measurements['temperature_celsius'].' °C' : 'No registrada' }}</dd></div>
            <div><dt class="text-sm font-semibold text-slate-500">Frecuencia respiratoria</dt><dd class="mt-1">{{ $measurements['respiratory_rate'] !== null ? $measurements['respiratory_rate'].' respiraciones/min' : 'No registrada' }}</dd></div>
            <div><dt class="text-sm font-semibold text-slate-500">Saturación de oxígeno</dt><dd class="mt-1">{{ $measurements['oxygen_saturation'] !== null ? $measurements['oxygen_saturation'].' %' : 'No registrada' }}</dd></div>
            <div><dt class="text-sm font-semibold text-slate-500">Peso</dt><dd class="mt-1">{{ $measurements['weight_kg'] !== null ? $measurements['weight_kg'].' kg' : 'No registrado' }}</dd></div>
            <div><dt class="text-sm font-semibold text-slate-500">Talla</dt><dd class="mt-1">{{ $measurements['height_cm'] !== null ? $measurements['height_cm'].' cm' : 'No registrada' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-sm font-semibold text-slate-500">Observaciones de enfermería</dt><dd class="mt-1 whitespace-pre-wrap">{{ $measurements['observations'] ?: 'Sin observaciones' }}</dd></div>
        </dl>
    </div>

    @if ($nursingIntake->amendments->isNotEmpty())
        <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="font-bold text-slate-900">Historial de versiones</h2>
            <ol class="mt-3 grid gap-3">
                <li>
                    @include('nursing.intakes._snapshot', [
                        'title' => 'Registro original',
                        'author' => $nursingIntake->nurse->name,
                        'recordedAt' => $nursingIntake->measured_at->format('d/m/Y H:i'),
                        'amendmentReason' => null,
                        'snapshot' => $nursingIntake->measurementData(),
                    ])
                </li>
                @foreach ($nursingIntake->amendments as $amendment)
                    <li>
                        @include('nursing.intakes._snapshot', [
                            'title' => 'Rectificación',
                            'author' => $amendment->author->name,
                            'recordedAt' => $amendment->created_at->format('d/m/Y H:i'),
                            'amendmentReason' => $amendment->reason,
                            'snapshot' => $amendment->data,
                        ])
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    <a href="{{ route('appointments.doctor-agenda', ['date' => $appointment->appointment_date->toDateString()]) }}" class="mt-5 inline-flex rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Volver a mi agenda</a>
</section>
@endsection
