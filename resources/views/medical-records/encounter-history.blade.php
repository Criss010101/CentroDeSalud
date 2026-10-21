@extends('layouts.app')

@section('title', 'Historial de atención')

@section('content')
<section>
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Historial clínico</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $patientProfile->display_name }}</h1>
    <p class="mt-2 text-slate-600">Documento: {{ $patientProfile->national_id }} · Sin cuenta de portal</p>

    <div class="mt-6 grid gap-4">
        @forelse ($careEncounters as $item)
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="font-bold text-slate-900">{{ $item->received_at?->format('d/m/Y H:i') }} · {{ $item->source === 'walk_in' ? 'Atención sin reserva' : 'Atención con cita' }}</h2>
                        <p class="mt-1 text-sm text-slate-600">Médico: {{ $item->doctor->name }} · Estado: {{ $item->status }}</p>
                    </div>
                    @if ($item->is($encounter) && ! $item->medicalConsultation)
                        <a href="{{ route('medical-consultations.encounters.create', $item) }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Registrar consulta</a>
                    @endif
                </div>

                @if ($item->nursingIntake)
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <h3 class="font-semibold text-slate-800">Recepción de enfermería</h3>
                        <p class="mt-1 text-sm text-slate-700">Motivo referido: {{ $item->nursingIntake->reason }}</p>
                        <p class="mt-1 text-sm text-slate-500">Registrado por {{ $item->nursingIntake->nurse->name }} · {{ $item->nursingIntake->measured_at->format('d/m/Y H:i') }}</p>
                        <a href="{{ route('nursing.encounters.doctor-show', $item) }}" class="mt-2 inline-flex text-sm font-semibold text-teal-800 hover:underline">Ver mediciones y rectificaciones</a>
                    </div>
                @endif

                @if ($item->medicalConsultation)
                    <div class="mt-4 border-t border-slate-100 pt-4 text-sm">
                        <h3 class="font-semibold text-slate-800">Consulta médica</h3>
                        <p class="mt-2"><span class="font-semibold">Motivo:</span> {{ $item->medicalConsultation->reason }}</p>
                        <p class="mt-1"><span class="font-semibold">Diagnóstico:</span> {{ $item->medicalConsultation->diagnosis }}</p>
                        <p class="mt-1"><span class="font-semibold">Tratamiento:</span> {{ $item->medicalConsultation->treatment }}</p>
                        <p class="mt-1"><span class="font-semibold">Recomendaciones:</span> {{ $item->medicalConsultation->recommendations }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-600">No hay atenciones clínicas previas para este paciente.</div>
        @endforelse
    </div>

    @if ($careEncounters->hasPages())
        <div class="mt-5">{{ $careEncounters->links() }}</div>
    @endif
    <a href="{{ route('medical-consultations.encounters.index') }}" class="mt-5 inline-flex rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Volver a atenciones</a>
</section>
@endsection
