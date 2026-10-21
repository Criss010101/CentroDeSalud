@extends('layouts.app')

@section('title', 'Mi ficha')

@section('content')
<div class="mx-auto max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Datos personales</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Mi ficha de paciente</h1>
    <p class="mt-2 text-slate-600">Mantén tus datos actualizados para facilitar tu atención.</p>
    <form method="POST" action="{{ route('patients.profile.update') }}" class="mt-8 grid gap-5 sm:grid-cols-2">
        @csrf
        @method('PUT')
        <div class="sm:col-span-2">
            <label for="name" class="mb-2 block text-sm font-medium">Nombre completo *</label>
            <input id="name" name="name" value="{{ old('name', $profile->user->name) }}" required autocomplete="name" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <div>
            <label for="email" class="mb-2 block text-sm font-medium">Correo electrónico *</label>
            <input id="email" name="email" type="email" value="{{ old('email', $profile->user->email) }}" required autocomplete="email" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <div>
            <label for="national_id" class="mb-2 block text-sm font-medium">Carnet de identidad *</label>
            <input id="national_id" name="national_id" value="{{ old('national_id', $profile->national_id) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <div>
            <label for="birth_date" class="mb-2 block text-sm font-medium">Fecha de nacimiento *</label>
            <input id="birth_date" name="birth_date" type="date" max="{{ now()->toDateString() }}" value="{{ old('birth_date', $profile->birth_date->format('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <div>
            <label for="sex" class="mb-2 block text-sm font-medium">Sexo *</label>
            <select id="sex" name="sex" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                <option value="F" @selected(old('sex', $profile->sex) === 'F')>Femenino</option>
                <option value="M" @selected(old('sex', $profile->sex) === 'M')>Masculino</option>
                <option value="O" @selected(old('sex', $profile->sex) === 'O')>Otro</option>
            </select>
        </div>
        <div>
            <label for="phone" class="mb-2 block text-sm font-medium">Teléfono</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone', $profile->phone) }}" autocomplete="tel" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <div class="sm:col-span-2">
            <label for="address" class="mb-2 block text-sm font-medium">Dirección</label>
            <input id="address" name="address" value="{{ old('address', $profile->address) }}" autocomplete="street-address" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <div class="pt-2 sm:col-span-2">
            <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection
