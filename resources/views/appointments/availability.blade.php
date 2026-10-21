@extends('layouts.app')

@section('title', 'Disponibilidad de citas')

@section('content')
<section>
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Citas médicas</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Busca un horario disponible</h1>
    <p class="mt-2 text-slate-600">Elige una especialidad y fecha para ver los médicos con espacios de 30 minutos libres.</p>

    <form method="GET" action="{{ route('appointments.availability') }}" class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <div>
            <label for="specialty_id" class="mb-2 block text-sm font-medium">Especialidad</label>
            <select id="specialty_id" name="specialty_id" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                <option value="">Todas las especialidades</option>
                @foreach ($specialties as $specialty)
                    <option value="{{ $specialty->id }}" @selected((string) request('specialty_id') === (string) $specialty->id)>{{ $specialty->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="date" class="mb-2 block text-sm font-medium">Fecha</label>
            <input id="date" name="date" type="date" min="{{ today()->toDateString() }}" value="{{ $date->toDateString() }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <button type="submit" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Buscar horarios</button>
    </form>

    <div class="mt-6 grid gap-4">
        @forelse ($doctors as $doctor)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $doctor->user->name }}</h2>
                        <p class="mt-1 text-sm text-slate-600">{{ $doctor->specialties->pluck('name')->join(' · ') ?: 'Sin especialidad activa' }}</p>
                    </div>
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800">{{ $date->format('d/m/Y') }}</span>
                </div>
                @if ($doctor->available_times)
                    <p class="mt-5 text-sm font-semibold text-slate-700">Horarios disponibles</p>
                    <div class="mt-3 flex flex-wrap gap-3">
                        @foreach ($doctor->available_times as $time)
                            <form method="POST" action="{{ route('appointments.store') }}" class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 p-3">
                                @csrf
                                <input type="hidden" name="staff_profile_id" value="{{ $doctor->id }}">
                                <input type="hidden" name="appointment_date" value="{{ $date->toDateString() }}">
                                <input type="hidden" name="start_time" value="{{ $time }}">
                                <span class="self-center font-semibold text-slate-800">{{ $time }}</span>
                                <label class="sr-only" for="reason-{{ $doctor->id }}-{{ str_replace(':', '', $time) }}">Motivo de consulta opcional</label>
                                <input id="reason-{{ $doctor->id }}-{{ str_replace(':', '', $time) }}" name="reason" maxlength="500" placeholder="Motivo (opcional)" class="w-44 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Reservar</button>
                            </form>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-500">No tiene horarios libres para esta fecha.</p>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-600">No hay médicos disponibles para esta búsqueda.</div>
        @endforelse
    </div>
</section>
@endsection
