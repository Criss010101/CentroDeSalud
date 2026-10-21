@extends('layouts.app')

@section('title', 'Atenciones sin reserva')

@section('content')
<section>
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Atención médica</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Atenciones presenciales sin reserva</h1>
    <p class="mt-2 text-slate-600">Atenciones del día asignadas a tu usuario. La consulta queda separada de la recepción y de los datos de enfermería.</p>
    <div class="mt-6 grid gap-4">
        @forelse ($encounters as $encounter)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $encounter->patientProfile->display_name }}</h2>
                        <p class="mt-1 text-sm text-slate-600">Documento: {{ $encounter->patientProfile->national_id }} · Recibido: {{ $encounter->received_at->format('H:i') }}</p>
                        <p class="mt-1 text-sm text-slate-600">Estado: {{ $encounter->status === 'waiting' ? 'En espera' : 'Pendiente de consulta' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @if ($encounter->nursingIntake)
                            <a href="{{ route('nursing.encounters.doctor-show', $encounter) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Datos de enfermería</a>
                        @endif
                        <a href="{{ route('medical-records.encounters.history', $encounter) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Historial</a>
                        @if (! $encounter->medicalConsultation)
                            <a href="{{ route('medical-consultations.encounters.create', $encounter) }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Registrar consulta</a>
                        @endif
                    </div>
                </div>
                @if ($encounter->nursingIntake)
                    <p class="mt-4 text-sm text-slate-700"><span class="font-semibold">Motivo referido:</span> {{ $encounter->nursingIntake->reason }}</p>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-600">No tienes atenciones sin reserva asignadas para hoy.</div>
        @endforelse
    </div>
</section>
@endsection
