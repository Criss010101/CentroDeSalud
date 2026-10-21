@extends('layouts.app')

@section('title', 'Datos iniciales de paciente presencial')

@section('content')
<section class="mx-auto max-w-4xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Atención médica · Sin reserva</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Datos iniciales de recepción</h1>
    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
        <dl class="grid gap-4 sm:grid-cols-3">
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paciente</dt><dd class="mt-1 font-semibold">{{ $encounter->patientProfile->display_name }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Documento</dt><dd class="mt-1 font-semibold">{{ $encounter->patientProfile->national_id }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registrado por</dt><dd class="mt-1 font-semibold">{{ $nursingIntake->nurse->name }}</dd></div>
        </dl>
        <p class="mt-4 text-sm text-slate-500">Mediciones registradas el {{ $nursingIntake->measured_at->format('d/m/Y H:i') }}. Estos datos iniciales no constituyen un diagnóstico médico.</p>
    </div>
    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 sm:p-8">
        <h2 class="text-xl font-bold text-slate-900">Versión vigente</h2>
        @if ($latestAmendment)
            <p class="mt-2 text-sm text-slate-600">Rectificada por {{ $latestAmendment->author->name }} el {{ $latestAmendment->created_at->format('d/m/Y H:i') }}. Motivo: {{ $latestAmendment->reason }}</p>
        @endif
        @include('nursing.intakes._snapshot', [
            'title' => 'Datos actuales',
            'author' => $latestAmendment?->author->name ?? $nursingIntake->nurse->name,
            'recordedAt' => ($latestAmendment?->created_at ?? $nursingIntake->measured_at)->format('d/m/Y H:i'),
            'amendmentReason' => null,
            'snapshot' => $measurements,
        ])
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
    <a href="{{ route('appointments.doctor-agenda') }}" class="mt-5 inline-flex rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Volver a mi agenda</a>
</section>
@endsection
