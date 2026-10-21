@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="mx-auto max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administración</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Editar cuenta</h1>
    <p class="mt-2 text-slate-600">Actualiza los datos y el rol de {{ $user->name }}.</p>
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-8">
        @csrf
        @method('PUT')
        @include('admin.users._form')
    </form>
</div>
@endsection
