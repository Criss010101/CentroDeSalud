@extends('layouts.app')

@section('title', 'Agenda médica')

@section('content')
<section>
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Atención médica</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Mi agenda</h1>
    <form method="GET" action="{{ route('appointments.doctor-agenda') }}" class="mt-6 flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-white p-5">
        <div>
            <label for="date" class="mb-2 block text-sm font-medium">Fecha</label>
            <input id="date" name="date" type="date" value="{{ $date->toDateString() }}" class="rounded-xl border border-slate-300 px-4 py-3">
        </div>
        <button type="submit" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Consultar</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-4 font-semibold text-slate-800">Citas del {{ $date->format('d/m/Y') }}</div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-semibold">Hora</th><th class="px-5 py-3 font-semibold">Paciente</th><th class="px-5 py-3 font-semibold">Contacto</th><th class="px-5 py-3 font-semibold">Estado</th><th class="px-5 py-3 font-semibold">Motivo</th><th class="px-5 py-3 font-semibold">Atención clínica</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td class="px-5 py-4 font-semibold">{{ substr($appointment->start_time, 0, 5) }}</td>
                            <td class="px-5 py-4">{{ $appointment->patient->name }}</td>
                            <td class="px-5 py-4">{{ $appointment->patient->email }}</td>
                            <td class="px-5 py-4">{{ ['confirmed' => 'Confirmada', 'completed' => 'Atendida', 'pending' => 'Pendiente'][$appointment->status] }}</td>
                            <td class="px-5 py-4">{{ $appointment->reason ?: '—' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-col items-start gap-2">
                                    <a href="{{ route('medical-records.history', $appointment->patient) }}" class="font-semibold text-teal-800 hover:underline">Historial clínico</a>
                                    @if ($appointment->nursingIntake)
                                        <a href="{{ route('nursing.intakes.doctor-show', $appointment) }}" class="text-sm font-medium text-slate-700 hover:underline">Ver datos iniciales</a>
                                    @endif
                                    @if ($appointment->medicalConsultation)
                                        <span class="text-sm text-slate-500">Consulta registrada</span>
                                    @elseif ($appointment->can_register_consultation)
                                        <a href="{{ route('medical-consultations.create', $appointment) }}" class="text-sm font-semibold text-teal-800 hover:underline">Registrar consulta</a>
                                    @elseif ($appointment->status === 'confirmed')
                                        <span class="text-sm text-slate-500">Disponible al iniciar la cita</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No hay citas programadas para esta fecha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
