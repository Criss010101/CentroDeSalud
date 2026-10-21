@extends('layouts.app')

@section('title', 'Especialidades')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administración</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Especialidades</h1>
        <p class="mt-2 text-slate-600">Catálogo de servicios médicos del centro.</p>
    </div>
    <a href="{{ route('admin.specialties.create') }}" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Nueva especialidad</a>
</div>

<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-semibold">Especialidad</th><th class="px-5 py-3 font-semibold">Médicos</th><th class="px-5 py-3 font-semibold">Estado</th><th class="px-5 py-3 font-semibold">Acciones</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($specialties as $specialty)
                    <tr>
                        <td class="px-5 py-4"><span class="font-semibold text-slate-900">{{ $specialty->name }}</span><span class="mt-1 block text-slate-500">{{ $specialty->description ?: 'Sin descripción' }}</span></td>
                        <td class="px-5 py-4">{{ $specialty->doctors_count }}</td>
                        <td class="px-5 py-4">{{ $specialty->is_active ? 'Activa' : 'Inactiva' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex gap-3">
                                <a class="font-semibold text-teal-800 hover:underline" href="{{ route('admin.specialties.edit', $specialty) }}">Editar</a>
                                <form method="POST" action="{{ route('admin.specialties.destroy', $specialty) }}" onsubmit="return confirm('¿Eliminar esta especialidad?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-red-700 hover:underline">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Aún no hay especialidades registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($specialties->hasPages())
        <div class="border-t border-slate-200 px-5 py-4">{{ $specialties->links() }}</div>
    @endif
</div>
@endsection
