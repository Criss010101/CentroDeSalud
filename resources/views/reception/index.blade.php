@extends('layouts.app')

@section('title', 'Atención y recepción')

@section('content')
<section>
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Admisión presencial</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Atención y recepción</h1>
    <p class="mt-2 text-slate-600">Registra la llegada sin marcar la consulta como realizada. Las urgencias siguen el protocolo clínico del centro y no deben esperar este registro administrativo.</p>

    <form method="GET" action="{{ route('reception.index') }}" class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-5">
        <div class="min-w-64 flex-1">
            <label for="q" class="mb-2 block text-sm font-medium">Código de ficha, documento o nombre</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" class="w-full rounded-xl border border-slate-300 px-4 py-3">
        </div>
        <button type="submit" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Buscar</button>
        @if (filled($filters['q'] ?? null))
            <a href="{{ route('reception.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Limpiar</a>
        @endif
    </form>

    @if ($appointmentMatches !== null)
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="font-bold text-slate-900">Reservas encontradas (máximo 20)</h2>
            <div class="mt-4 grid gap-3">
                @forelse ($appointmentMatches as $match)
                    <article class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 p-4">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $match->patient->name }} · {{ $match->booking_code }}</p>
                            <p class="text-sm text-slate-600">{{ $match->patient->patientProfile?->national_id }} · {{ $match->appointment_date->format('d/m/Y') }} {{ substr($match->start_time, 0, 5) }} · {{ $match->doctor->user->name }}</p>
                        </div>
                        @if (! $match->careEncounter && $match->appointment_date->isSameDay(today()) && in_array($match->status, ['pending', 'confirmed'], true))
                            <form method="POST" action="{{ route('reception.appointments.check-in', $match) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Registrar llegada</button>
                            </form>
                        @else
                            <span class="text-sm text-slate-600">{{ $match->careEncounter?->status ?? $match->status }}</span>
                        @endif
                    </article>
                @empty
                    <p class="text-sm text-slate-600">No se encontraron reservas.</p>
                @endforelse
            </div>

            @if ($patientMatches->isNotEmpty())
                <div class="mt-5 border-t border-slate-200 pt-4">
                    <h3 class="font-semibold text-slate-900">Fichas coincidentes</h3>
                    <ul class="mt-2 grid gap-3 text-sm text-slate-700">
                        @foreach ($patientMatches as $profile)
                            <li class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 p-4">
                                <span>{{ $profile->display_name }} · Documento {{ $profile->national_id }} · {{ $profile->user?->email ?? 'Sin cuenta de portal' }}</span>
                                <form method="POST" action="{{ route('reception.walk-ins.store') }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="patient_profile_id" value="{{ $profile->id }}">
                                    <label for="existing_doctor_user_id_{{ $profile->id }}" class="sr-only">Médico responsable para {{ $profile->display_name }}</label>
                                    <select id="existing_doctor_user_id_{{ $profile->id }}" name="doctor_user_id" required class="rounded-lg border border-slate-300 bg-white px-3 py-2">
                                        <option value="">Selecciona un médico</option>
                                        @foreach ($doctors as $doctor)
                                            <option value="{{ $doctor->id }}">{{ $doctor->name }} · {{ $doctor->staffProfile->specialties->pluck('name')->join(', ') }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Iniciar atención</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-sm text-amber-800">Verifica la identidad antes de iniciar la atención. Si la ficha ya existe, selecciónala en lugar de crear otra.</p>
                </div>
            @endif
        </section>
    @endif

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-4 text-sm font-medium text-slate-600">Reservas activas de hoy · {{ today()->format('d/m/Y') }}</div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3 font-semibold">Hora / código</th><th class="px-5 py-3 font-semibold">Paciente</th><th class="px-5 py-3 font-semibold">Especialidad y médico</th><th class="px-5 py-3 font-semibold">Estado de admisión</th><th class="px-5 py-3 font-semibold">Acción</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td class="px-5 py-4"><span class="font-semibold">{{ substr($appointment->start_time, 0, 5) }}</span><span class="mt-1 block text-xs text-teal-800">{{ $appointment->booking_code }}</span></td>
                            <td class="px-5 py-4">{{ $appointment->patient->name }}<span class="mt-1 block text-slate-500">{{ $appointment->patient->patientProfile?->national_id }}</span></td>
                            <td class="px-5 py-4">{{ $appointment->doctor->specialties->pluck('name')->join(', ') }}<span class="mt-1 block text-slate-500">{{ $appointment->doctor->user->name }}</span></td>
                            <td class="px-5 py-4">{{ $appointment->careEncounter?->status ? ['waiting' => 'En espera', 'in_nursing' => 'En enfermería', 'pending_consultation' => 'Pendiente de consulta', 'in_consultation' => 'En consulta', 'attended' => 'Atendida'][$appointment->careEncounter->status] ?? $appointment->careEncounter->status : 'Sin llegada registrada' }}</td>
                            <td class="px-5 py-4">
                                @if (! $appointment->careEncounter)
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('reception.appointments.check-in', $appointment) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-teal-700 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-800">Registrar llegada</button>
                                        </form>
                                        @if (\Carbon\Carbon::parse($appointment->appointment_date->toDateString().' '.$appointment->start_time)->isPast())
                                            <form method="POST" action="{{ route('reception.appointments.no-show', $appointment) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">No se presentó</button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-slate-600">Llegada {{ $appointment->careEncounter->received_at?->format('H:i') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No hay reservas activas para hoy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Sin reserva</p>
        <h2 class="mt-2 text-2xl font-bold text-slate-900">Registrar una atención presencial</h2>
        <p class="mt-2 text-sm text-slate-600">Si no existe una ficha coincidente, registra los datos mínimos para crearla sin cuenta de portal. El documento es obligatorio en esta primera versión; no se inventan identificadores personales.</p>

        <form method="POST" action="{{ route('reception.walk-ins.store') }}" class="mt-5 grid gap-5 sm:grid-cols-2">
            @csrf
            <div>
                <label for="name" class="mb-2 block text-sm font-medium">Nombre completo *</label>
                <input id="name" name="name" value="{{ old('name') }}" maxlength="255" required class="w-full rounded-xl border border-slate-300 px-4 py-3">
            </div>
            <div>
                <label for="national_id" class="mb-2 block text-sm font-medium">Documento de identidad *</label>
                <input id="national_id" name="national_id" value="{{ old('national_id') }}" maxlength="30" required class="w-full rounded-xl border border-slate-300 px-4 py-3">
            </div>
            <div>
                <label for="birth_date" class="mb-2 block text-sm font-medium">Fecha de nacimiento *</label>
                <input id="birth_date" name="birth_date" type="date" max="{{ today()->toDateString() }}" value="{{ old('birth_date') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3">
            </div>
            <div>
                <label for="sex" class="mb-2 block text-sm font-medium">Sexo *</label>
                <select id="sex" name="sex" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                    <option value="">Selecciona</option>
                    <option value="F" @selected(old('sex') === 'F')>Femenino</option>
                    <option value="M" @selected(old('sex') === 'M')>Masculino</option>
                    <option value="O" @selected(old('sex') === 'O')>Otro</option>
                </select>
            </div>
            <div>
                <label for="phone" class="mb-2 block text-sm font-medium">Teléfono</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" maxlength="30" class="w-full rounded-xl border border-slate-300 px-4 py-3">
            </div>
            <div>
                <label for="doctor_user_id" class="mb-2 block text-sm font-medium">Médico responsable *</label>
                <select id="doctor_user_id" name="doctor_user_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                    <option value="">Selecciona un médico</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) old('doctor_user_id') === (string) $doctor->id)>{{ $doctor->name }} · {{ $doctor->staffProfile->specialties->pluck('name')->join(', ') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="address" class="mb-2 block text-sm font-medium">Dirección</label>
                <input id="address" name="address" value="{{ old('address') }}" maxlength="255" class="w-full rounded-xl border border-slate-300 px-4 py-3">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">Crear ficha e iniciar atención</button>
            </div>
        </form>
    </section>
</section>
@endsection
