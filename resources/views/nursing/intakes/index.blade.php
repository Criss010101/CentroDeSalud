@extends('layouts.app')

@section('title', 'Recepción de enfermería')

@section('content')
<section>
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Atención inicial</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Recepción de enfermería</h1>
    <p class="mt-2 text-slate-600">Citas pendientes y confirmadas de hoy. Registra solo los datos que hayas recibido o medido.</p>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-4 text-sm font-medium text-slate-600">
            {{ $appointments->count() }} cita(s) para {{ today()->format('d/m/Y') }}
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Hora</th>
                        <th class="px-5 py-3 font-semibold">Paciente</th>
                        <th class="px-5 py-3 font-semibold">Médico responsable</th>
                        <th class="px-5 py-3 font-semibold">Estado de recepción</th>
                        <th class="px-5 py-3 font-semibold">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td class="px-5 py-4 font-semibold">{{ substr($appointment->start_time, 0, 5) }}</td>
                            <td class="px-5 py-4">{{ $appointment->patient->name }}</td>
                            <td class="px-5 py-4">{{ $appointment->doctor->user->name }}</td>
                            <td class="px-5 py-4">
                                @if ($appointment->nursingIntake)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Datos registrados</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('nursing.intakes.edit', $appointment) }}" class="font-semibold text-teal-800 hover:underline">
                                    {{ $appointment->nursingIntake ? 'Ver o rectificar' : 'Registrar datos' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No hay citas pendientes o confirmadas para hoy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-4 text-sm font-medium text-slate-600">
            Atenciones sin reserva · {{ $walkInEncounters->count() }}
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Llegada</th>
                        <th class="px-5 py-3 font-semibold">Paciente</th>
                        <th class="px-5 py-3 font-semibold">Médico responsable</th>
                        <th class="px-5 py-3 font-semibold">Estado</th>
                        <th class="px-5 py-3 font-semibold">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($walkInEncounters as $encounter)
                        <tr>
                            <td class="px-5 py-4 font-semibold">{{ $encounter->received_at?->format('H:i') }}</td>
                            <td class="px-5 py-4">{{ $encounter->patientProfile->display_name }}<span class="mt-1 block text-slate-500">{{ $encounter->patientProfile->national_id }}</span></td>
                            <td class="px-5 py-4">{{ $encounter->doctor->name }}</td>
                            <td class="px-5 py-4">{{ $encounter->status === 'waiting' ? 'En espera' : 'En enfermería' }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('nursing.encounters.edit', $encounter) }}" class="font-semibold text-teal-800 hover:underline">
                                    {{ $encounter->nursingIntake ? 'Ver o rectificar' : 'Registrar datos' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No hay atenciones sin reserva pendientes para hoy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
