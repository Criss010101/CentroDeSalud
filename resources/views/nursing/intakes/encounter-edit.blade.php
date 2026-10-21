@extends('layouts.app')

@section('title', 'Datos iniciales de atención presencial')

@section('content')
<section class="mx-auto max-w-4xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Recepción de enfermería · Sin reserva</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $encounter->nursingIntake ? 'Rectificar datos iniciales' : 'Registrar datos iniciales' }}</h1>

    <div class="mt-5 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-3">
        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paciente</p><p class="mt-1 font-semibold">{{ $encounter->patientProfile->display_name }}</p></div>
        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Documento</p><p class="mt-1 font-semibold">{{ $encounter->patientProfile->national_id }}</p></div>
        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Médico responsable</p><p class="mt-1 font-semibold">{{ $encounter->doctor->name }}</p></div>
    </div>

    @if ($encounter->nursingIntake)
        <div class="mt-5 rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sm text-sky-900">
            El registro original se conserva. Las rectificaciones se añaden con autor, fecha y motivo.
            <span class="mt-2 block">Registrado por {{ $encounter->nursingIntake->nurse->name }} el {{ $encounter->nursingIntake->measured_at->format('d/m/Y H:i') }}.</span>
        </div>
        @if ($encounter->nursingIntake->amendments->isNotEmpty())
            <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-slate-900">Historial de rectificaciones</h2>
                <ol class="mt-3 grid gap-3">
                    <li>
                        @include('nursing.intakes._snapshot', [
                            'title' => 'Registro original',
                            'author' => $encounter->nursingIntake->nurse->name,
                            'recordedAt' => $encounter->nursingIntake->measured_at->format('d/m/Y H:i'),
                            'amendmentReason' => null,
                            'snapshot' => $encounter->nursingIntake->measurementData(),
                        ])
                    </li>
                    @foreach ($encounter->nursingIntake->amendments as $amendment)
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
        <form method="POST" action="{{ route('nursing.intakes.amendments.store', $encounter->nursingIntake) }}" class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @csrf
            <h2 class="mb-2 text-xl font-bold text-slate-900">Agregar rectificación</h2>
            @include('nursing.intakes._measurements')
            <div class="mt-5">
                <label for="correction_reason" class="mb-2 block text-sm font-medium">Motivo de la rectificación *</label>
                <textarea id="correction_reason" name="correction_reason" rows="2" required maxlength="500" class="w-full rounded-xl border border-slate-300 px-4 py-3">{{ old('correction_reason') }}</textarea>
            </div>
            <button type="submit" class="mt-5 rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">Guardar rectificación</button>
        </form>
    @else
        <form method="POST" action="{{ route('nursing.encounters.store', $encounter) }}" class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @csrf
            <p class="mb-5 text-sm text-slate-600">Registra únicamente mediciones efectivamente tomadas. No se generan diagnósticos ni recomendaciones automáticas.</p>
            @include('nursing.intakes._measurements')
            <button type="submit" class="mt-6 rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">Guardar y enviar al médico</button>
        </form>
    @endif
</section>
@endsection
