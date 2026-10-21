@extends('layouts.app')

@section('title', 'Mis citas')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Citas médicas</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Mis citas</h1>
        <p class="mt-2 text-slate-600">Revisa, confirma o cancela tus reservas.</p>
    </div>
    <a href="{{ route('appointments.availability') }}" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Buscar disponibilidad</a>
</div>

<div class="mt-6 grid gap-4">
    @forelse ($appointments as $appointment)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $appointment->doctor->user->name }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $appointment->doctor->specialties->pluck('name')->join(' · ') }}</p>
                    <p class="mt-3 text-sm text-slate-700">{{ $appointment->appointment_date->format('d/m/Y') }} a las {{ substr($appointment->start_time, 0, 5) }}</p>
                    @if ($appointment->reason)
                        <p class="mt-2 text-sm text-slate-500">Motivo: {{ $appointment->reason }}</p>
                    @endif
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ ['pending' => 'bg-amber-100 text-amber-800', 'confirmed' => 'bg-emerald-100 text-emerald-800', 'completed' => 'bg-sky-100 text-sky-800', 'cancelled' => 'bg-slate-100 text-slate-600'][$appointment->status] }}">
                    {{ ['pending' => 'Pendiente', 'confirmed' => 'Confirmada', 'completed' => 'Atendida', 'cancelled' => 'Cancelada'][$appointment->status] }}
                </span>
            </div>
            @if ($appointment->can_confirm || $appointment->can_cancel)
                <div class="mt-4 flex flex-wrap gap-3">
                    @if ($appointment->can_confirm)
                        <form method="POST" action="{{ route('appointments.confirm', $appointment) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Confirmar cita</button>
                        </form>
                    @endif
                    @if ($appointment->can_cancel)
                        <form method="POST" action="{{ route('appointments.cancel', $appointment) }}" onsubmit="return confirm('¿Cancelar esta cita?')">
                            @csrf
                            <button type="submit" class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Cancelar</button>
                        </form>
                    @endif
                </div>
            @endif
        </article>
    @empty
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-600">Todavía no tienes citas registradas.</div>
    @endforelse
</div>
@if ($appointments->hasPages())
    <div class="mt-6">{{ $appointments->links() }}</div>
@endif
@endsection
