@extends('layouts.app')

@section('title', 'Personal')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administración</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Personal del centro</h1>
        <p class="mt-2 text-slate-600">Gestiona médicos, enfermería y personal administrativo.</p>
    </div>
    <a href="{{ route('admin.staff.create') }}" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">Registrar personal</a>
</div>

<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3 font-semibold">Personal</th><th class="px-5 py-3 font-semibold">Cargo</th><th class="px-5 py-3 font-semibold">N.º laboral</th><th class="px-5 py-3 font-semibold">Especialidades</th><th class="px-5 py-3 font-semibold">Acciones</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($staff as $member)
                    <tr>
                        <td class="px-5 py-4"><span class="font-semibold text-slate-900">{{ $member->user->name }}</span><span class="mt-1 block text-slate-500">{{ $member->user->email }}</span></td>
                        <td class="px-5 py-4">{{ $member->user->role?->name }} · {{ $member->position }}</td>
                        <td class="px-5 py-4">{{ $member->employee_number }}</td>
                        <td class="px-5 py-4">{{ $member->specialties->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-3">
                                <a class="font-semibold text-teal-800 hover:underline" href="{{ route('admin.staff.edit', $member) }}">Editar</a>
                                @if ($member->user->hasRole('doctor'))
                                    <a class="font-semibold text-teal-800 hover:underline" href="{{ route('admin.staff.schedules.edit', $member) }}">Horarios</a>
                                @endif
                                <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" onsubmit="return confirm('¿Eliminar este registro y su cuenta?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="font-semibold text-red-700 hover:underline" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Aún no hay personal registrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($staff->hasPages())
        <div class="border-t border-slate-200 px-5 py-4">{{ $staff->links() }}</div>
    @endif
</div>
@endsection
