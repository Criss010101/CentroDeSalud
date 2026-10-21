@extends('layouts.app')

@section('title', 'Reportes institucionales')

@section('content')
<section>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administración</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Reportes institucionales</h1>
        </div>
        <a href="{{ route('admin.reports.export', ['month' => $month]) }}" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Descargar CSV</a>
    </div>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="mt-6 flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-white p-5">
        <div>
            <label for="month" class="mb-2 block text-sm font-medium">Mes del reporte</label>
            <input id="month" name="month" type="month" value="{{ $month }}" required class="rounded-xl border border-slate-300 px-4 py-3">
        </div>
        <button type="submit" class="rounded-xl bg-slate-800 px-5 py-3 font-semibold text-white hover:bg-slate-900">Consultar</button>
    </form>

    <p class="mt-4 text-sm text-slate-600">
        Resumen agregado para {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->locale('es')->translatedFormat('F Y') }}.
        El indicador de atención representa consultas completadas sobre citas asignadas; no evalúa resultados clínicos ni calidad profesional.
    </p>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Pacientes registrados durante el mes</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($report['patient_count']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Citas del mes</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($report['appointment_count']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Consultas completadas</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($report['appointment_counts']['completed']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Citas pendientes</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($report['appointment_counts']['pending']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Citas confirmadas</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($report['appointment_counts']['confirmed']) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Citas canceladas</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($report['appointment_counts']['cancelled']) }}</p>
        </article>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-bold text-slate-900">Actividad por médico</h2>
            <p class="mt-1 text-sm text-slate-500">Citas asignadas y consultas médicas completadas en el mes seleccionado.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Médico</th>
                        <th class="px-5 py-3 font-semibold">Citas asignadas</th>
                        <th class="px-5 py-3 font-semibold">Consultas completadas</th>
                        <th class="px-5 py-3 font-semibold">Atención (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($report['doctors'] as $doctor)
                        <tr>
                            <td class="px-5 py-4 font-medium">{{ $doctor->user->name }}</td>
                            <td class="px-5 py-4">{{ number_format($doctor->appointments_count) }}</td>
                            <td class="px-5 py-4">{{ number_format($doctor->completed_appointments_count) }}</td>
                            <td class="px-5 py-4">{{ number_format($doctor->attendance_rate, 1) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No hay médicos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
