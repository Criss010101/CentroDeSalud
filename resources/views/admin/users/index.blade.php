@extends('layouts.app')

@section('title', 'Gestión de usuarios')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administración</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Usuarios y roles</h1>
        <p class="mt-2 text-slate-600">Administra las cuentas y permisos del sistema.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white hover:bg-teal-800">+ Crear usuario</a>
</div>

<div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3 font-semibold">Usuario</th><th class="px-5 py-3 font-semibold">Rol</th><th class="px-5 py-3 font-semibold">Carnet</th><th class="px-5 py-3 font-semibold">Acciones</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4"><span class="font-semibold text-slate-900">{{ $user->name }}</span><span class="mt-1 block text-slate-500">{{ $user->email }}</span></td>
                        <td class="px-5 py-4"><span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800">{{ $user->role?->name ?? 'Sin rol' }}</span></td>
                        <td class="px-5 py-4">{{ $user->patientProfile?->national_id ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="font-semibold text-teal-800 hover:underline">Editar</a>
                                @unless ($user->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('¿Eliminar la cuenta de {{ $user->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-red-700 hover:underline">Eliminar</button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Aún no hay usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="border-t border-slate-200 px-5 py-4">{{ $users->links() }}</div>
    @endif
</div>
@endsection
