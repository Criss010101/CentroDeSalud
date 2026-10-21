@extends('layouts.app')

@section('title', 'Directorio de pacientes')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Atención</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Directorio de pacientes</h1>
        <p class="mt-2 text-slate-600">Busca por nombre, correo o carnet de identidad.</p>
    </div>
</div>

<form method="GET" action="{{ route('patients.index') }}" class="mt-8 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-[1fr_13rem_auto] sm:items-end">
    <div>
        <label for="q" class="mb-2 block text-sm font-medium">Nombre, correo o carnet</label>
        <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Ej. 1234567 o María López" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="sex" class="mb-2 block text-sm font-medium">Sexo</label>
        <select id="sex" name="sex" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
            <option value="">Todos</option>
            <option value="F" @selected(($filters['sex'] ?? '') === 'F')>Femenino</option>
            <option value="M" @selected(($filters['sex'] ?? '') === 'M')>Masculino</option>
            <option value="O" @selected(($filters['sex'] ?? '') === 'O')>Otro</option>
        </select>
    </div>
    <div class="flex gap-3">
        <button type="submit" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Buscar</button>
        <a href="{{ route('patients.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Limpiar</a>
    </div>
</form>

<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="border-b border-slate-200 px-5 py-4 text-sm text-slate-600">{{ $patients->total() }} paciente(s) encontrado(s)</div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3 font-semibold">Paciente</th><th class="px-5 py-3 font-semibold">Carnet</th><th class="px-5 py-3 font-semibold">Nacimiento</th><th class="px-5 py-3 font-semibold">Sexo</th><th class="px-5 py-3 font-semibold">Contacto</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($patients as $patient)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4"><span class="font-semibold text-slate-900">{{ $patient->display_name }}</span><span class="mt-1 block text-slate-500">{{ $patient->user?->email ?? 'Sin cuenta de portal' }}</span></td>
                        <td class="px-5 py-4">{{ $patient->national_id }}</td>
                        <td class="px-5 py-4">{{ $patient->birth_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-4">{{ ['F' => 'Femenino', 'M' => 'Masculino', 'O' => 'Otro'][$patient->sex] ?? '—' }}</td>
                        <td class="px-5 py-4">{{ $patient->phone ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No se encontraron pacientes con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($patients->hasPages())
        <div class="border-t border-slate-200 px-5 py-4">{{ $patients->links() }}</div>
    @endif
</div>
@endsection
