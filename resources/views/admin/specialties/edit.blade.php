@extends('layouts.app')

@section('title', 'Editar especialidad')

@section('content')
<section class="mx-auto max-w-3xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administración</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Editar especialidad</h1>
    <form method="POST" action="{{ route('admin.specialties.update', $specialty) }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        @csrf
        @method('PUT')
        @include('admin.specialties._form')
    </form>
</section>
@endsection
