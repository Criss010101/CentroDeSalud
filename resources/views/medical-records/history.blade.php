@extends('layouts.app')

@section('title', 'Historial clínico')

@section('content')
<section class="mx-auto max-w-5xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Atención médica</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Historial clínico</h1>

    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-xl font-bold text-slate-900">{{ $patient->name }}</h2>
        <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-3">
            <div>
                <dt class="font-semibold text-slate-500">Correo</dt>
                <dd class="mt-1">{{ $patient->email }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-500">Documento</dt>
                <dd class="mt-1">{{ $patient->patientProfile?->national_id ?: 'No registrado' }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-500">Fecha de nacimiento</dt>
                <dd class="mt-1">{{ $patient->patientProfile?->birth_date?->format('d/m/Y') ?? 'No registrada' }}</dd>
            </div>
        </dl>
        <p class="mt-4 text-sm text-slate-500">Información clínica confidencial. Consulta únicamente los datos necesarios para la atención del paciente.</p>
    </div>

    @if (session('status'))
        <div role="status" class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mt-6 grid gap-5">
        @forelse ($appointments as $appointment)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Atención del {{ $appointment->appointment_date->format('d/m/Y') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ substr($appointment->start_time, 0, 5) }} · Dr./Dra. {{ $appointment->doctor->user->name }}
                        </p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        {{ $appointment->medicalConsultation ? 'Consulta médica' : 'Datos iniciales' }}
                    </span>
                </div>

                @if ($appointment->medicalConsultation)
                    <div class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <h3 class="font-semibold text-slate-500">Motivo de consulta</h3>
                            <p class="mt-1 whitespace-pre-wrap">{{ $appointment->medicalConsultation->reason }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-500">Diagnóstico</h3>
                            <p class="mt-1 whitespace-pre-wrap">{{ $appointment->medicalConsultation->diagnosis }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <h3 class="font-semibold text-slate-500">Observaciones clínicas</h3>
                            <p class="mt-1 whitespace-pre-wrap">{{ $appointment->medicalConsultation->observations }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-500">Tratamiento</h3>
                            <p class="mt-1 whitespace-pre-wrap">{{ $appointment->medicalConsultation->treatment }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-500">Recomendaciones</h3>
                            <p class="mt-1 whitespace-pre-wrap">{{ $appointment->medicalConsultation->recommendations }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-slate-500">
                        Registrada por {{ $appointment->medicalConsultation->doctor->name }}
                        el {{ $appointment->medicalConsultation->created_at->format('d/m/Y H:i') }}.
                    </p>
                @endif

                @if ($appointment->nursingIntake)
                    @php
                        $latestAmendment = $appointment->nursingIntake->amendments->last();
                        $measurements = $latestAmendment?->data ?? $appointment->nursingIntake->measurementData();
                    @endphp
                    <div class="mt-5 rounded-xl bg-teal-50/70 p-4">
                        <h3 class="font-semibold text-teal-950">Datos iniciales de enfermería</h3>
                        <p class="mt-1 text-xs text-slate-600">
                            Registrados por {{ $appointment->nursingIntake->nurse->name }}
                            el {{ $appointment->nursingIntake->measured_at->format('d/m/Y H:i') }}.
                            No constituyen un diagnóstico médico.
                        </p>
                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="font-medium text-slate-500">Motivo referido</dt>
                                <dd class="mt-1 whitespace-pre-wrap">{{ $measurements['reason'] }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">Presión arterial</dt>
                                <dd class="mt-1">{{ ($measurements['blood_pressure_systolic'] ?? null) !== null ? $measurements['blood_pressure_systolic'].' / '.$measurements['blood_pressure_diastolic'].' mmHg' : 'No registrada' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">Frecuencia cardíaca</dt>
                                <dd class="mt-1">{{ ($measurements['heart_rate'] ?? null) !== null ? $measurements['heart_rate'].' latidos/min' : 'No registrada' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">Temperatura</dt>
                                <dd class="mt-1">{{ ($measurements['temperature_celsius'] ?? null) !== null ? $measurements['temperature_celsius'].' °C' : 'No registrada' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">Frecuencia respiratoria</dt>
                                <dd class="mt-1">{{ ($measurements['respiratory_rate'] ?? null) !== null ? $measurements['respiratory_rate'].' respiraciones/min' : 'No registrada' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">Saturación de oxígeno</dt>
                                <dd class="mt-1">{{ ($measurements['oxygen_saturation'] ?? null) !== null ? $measurements['oxygen_saturation'].' %' : 'No registrada' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">Peso / talla</dt>
                                <dd class="mt-1">{{ $measurements['weight_kg'] ?? '—' }} kg / {{ $measurements['height_cm'] ?? '—' }} cm</dd>
                            </div>
                            @if ($measurements['observations'] ?? null)
                                <div class="sm:col-span-2">
                                    <dt class="font-medium text-slate-500">Observaciones de enfermería</dt>
                                    <dd class="mt-1 whitespace-pre-wrap">{{ $measurements['observations'] }}</dd>
                                </div>
                            @endif
                        </dl>
                        @if ($latestAmendment)
                            <p class="mt-3 text-xs text-slate-600">
                                Versión vigente rectificada por {{ $latestAmendment->author->name }}:
                                {{ $latestAmendment->reason }}. El historial completo de versiones se conserva en el registro de recepción.
                            </p>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-600">
                Todavía no hay consultas médicas ni datos iniciales registrados para este paciente.
            </div>
        @endforelse
    </div>

    @if ($appointments->hasPages())
        <div class="mt-6">{{ $appointments->links() }}</div>
    @endif

    <a href="{{ route('appointments.doctor-agenda') }}" class="mt-6 inline-flex rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Volver a mi agenda</a>
</section>
@endsection
