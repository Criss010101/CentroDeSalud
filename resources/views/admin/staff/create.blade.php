@extends('layouts.app')

@section('title', 'Registrar personal')

@section('content')
<section class="mx-auto max-w-4xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administración</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Registrar personal</h1>
    <p class="mt-2 text-slate-600">Se creará una cuenta de acceso con permisos acordes al tipo de personal.</p>
    <form method="POST" action="{{ route('admin.staff.store') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        @csrf
        @include('admin.staff._form')
    </form>
</section>
@endsection
