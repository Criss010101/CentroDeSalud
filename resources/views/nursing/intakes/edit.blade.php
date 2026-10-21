@extends('layouts.app')

@section('title', 'Datos iniciales del paciente')

@section('content')
<section class="mx-auto max-w-4xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Recepción de enfermería</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $appointment->nursingIntake ? 'Revisar o rectificar datos iniciales' : 'Registrar datos iniciales' }}</h1>

    <div class="mt-5 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-3">
        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paciente</p><p class="mt-1 font-semibold text-slate-900">{{ $appointment->patient->name }}</p></div>
        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Médico responsable</p><p class="mt-1 font-semibold text-slate-900">{{ $appointment->doctor->user->name }}</p></div>
        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cita</p><p class="mt-1 font-semibold text-slate-900">{{ $appointment->appointment_date->format('d/m/Y') }} · {{ substr($appointment->start_time, 0, 5) }}</p></div>
    </div>

    @if ($appointment->nursingIntake)
        <div class="mt-5 rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sm text-sky-900">
            Este registro original no se modifica ni elimina. Las correcciones se guardan como rectificaciones anexas con autor, fecha y motivo.
            <span class="mt-2 block">Registrado por {{ $appointment->nursingIntake->nurse->name }} el {{ $appointment->nursingIntake->measured_at->format('d/m/Y H:i') }}.</span>
        </div>

        @if ($appointment->nursingIntake->amendments->isNotEmpty())
            <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-slate-900">Historial de rectificaciones</h2>
                <ol class="mt-3 grid gap-3">
                    <li>
                        @include('nursing.intakes._snapshot', [
                            'title' => 'Registro original',
                            'author' => $appointment->nursingIntake->nurse->name,
                            'recordedAt' => $appointment->nursingIntake->measured_at->format('d/m/Y H:i'),
                            'amendmentReason' => null,
                            'snapshot' => $appointment->nursingIntake->measurementData(),
                        ])
                    </li>
                    @foreach ($appointment->nursingIntake->amendments as $amendment)
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

        <form method="POST" action="{{ route('nursing.intakes.amendments.store', $appointment->nursingIntake) }}" class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @csrf
            <h2 class="mb-2 text-xl font-bold text-slate-900">Agregar rectificación</h2>
            <p class="mb-5 text-sm text-slate-600">Los campos muestran la versión vigente. Al guardar, se añade una nueva versión sin sobrescribir los datos previos.</p>
            @include('nursing.intakes._measurements')
            <div class="mt-5">
                <label for="correction_reason" class="mb-2 block text-sm font-medium">Motivo de la rectificación *</label>
                <textarea id="correction_reason" name="correction_reason" rows="2" required maxlength="500" class="w-full rounded-xl border border-slate-300 px-4 py-3">{{ old('correction_reason') }}</textarea>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">Guardar rectificación</button>
                <a href="{{ route('nursing.intakes.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Volver a recepción</a>
            </div>
        </form>
    @else
        <form method="POST" action="{{ route('nursing.intakes.store', $appointment) }}" class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @csrf
            <p class="mb-5 text-sm text-slate-600">Registra únicamente mediciones efectivamente tomadas. No se generarán diagnósticos ni recomendaciones automáticas.</p>
            @include('nursing.intakes._measurements')
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">Guardar y enviar al médico</button>
                <a href="{{ route('nursing.intakes.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    @endif
</section>
@endsection
