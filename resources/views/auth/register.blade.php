@extends('layouts.app')

@section('title', 'Registro de paciente')

@section('content')
<div class="flex w-full items-center justify-center p-3 sm:p-6 lg:h-full lg:min-h-0 lg:p-0">
    <div class="grid w-full max-w-6xl overflow-hidden rounded-3xl border border-white/50 bg-white/10 shadow-2xl shadow-teal-950/20 lg:h-full lg:max-w-[1600px] lg:min-h-0 lg:grid-cols-[0.92fr_1.08fr]">
        @include('auth._brand-panel')

        <section class="flex items-center justify-center bg-white p-5 sm:p-8 lg:min-h-0 lg:overflow-y-auto lg:px-8 lg:py-3">
            <div class="auth-register-content w-full max-w-xl">
                <div class="mb-5 flex items-start gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-teal-50 text-teal-800">
                        <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-3v6m-3-3h6"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-700">Crear cuenta nueva</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Registro de paciente</h2>
                        <p class="mt-1 text-sm text-slate-500">Completa tus datos para crear tu cuenta y ficha personal.</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div role="alert" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-semibold">Revisa los datos ingresados:</p>
                        <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="space-y-4 lg:space-y-1">
                    @csrf
                    <fieldset>
                        <legend class="mb-2 text-xs font-bold uppercase tracking-[0.15em] text-slate-500">Datos personales</legend>
                        <div class="grid gap-3 sm:grid-cols-2 lg:gap-y-1">
                            <div class="sm:col-span-2">
                                <label for="name" class="mb-1 block text-xs font-semibold text-slate-700">Nombre completo <span class="text-teal-700">*</span></label>
                                <div class="flex items-center gap-2 rounded-xl border border-teal-100 px-3 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                                    <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
                                    <input id="name" name="name" value="{{ old('name') }}" placeholder="Ej. María López García" required autocomplete="name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" class="min-w-0 w-full border-0 bg-transparent py-2.5 text-sm outline-none placeholder:text-slate-400">
                                </div>
                            </div>
                            <div>
                                <label for="email" class="mb-1 block text-xs font-semibold text-slate-700">Correo electrónico <span class="text-teal-700">*</span></label>
                                <div class="flex items-center gap-2 rounded-xl border border-teal-100 px-3 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                                    <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="ejemplo@correo.com" required autocomplete="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="min-w-0 w-full border-0 bg-transparent py-2.5 text-sm outline-none placeholder:text-slate-400">
                                </div>
                            </div>
                            <div>
                                <label for="national_id" class="mb-1 block text-xs font-semibold text-slate-700">Carnet de identidad <span class="text-teal-700">*</span></label>
                                <div class="flex items-center gap-2 rounded-xl border border-teal-100 px-3 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                                    <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16a3 3 0 0 1 6 0m3-5h3m-3 3h3"/></svg>
                                    <input id="national_id" name="national_id" value="{{ old('national_id') }}" placeholder="Ej. 12345678" required autocomplete="off" aria-invalid="{{ $errors->has('national_id') ? 'true' : 'false' }}" class="min-w-0 w-full border-0 bg-transparent py-2.5 text-sm outline-none placeholder:text-slate-400">
                                </div>
                            </div>
                            <div>
                                <label for="birth_date" class="mb-1 block text-xs font-semibold text-slate-700">Fecha de nacimiento <span class="text-teal-700">*</span></label>
                                <div class="flex items-center gap-2 rounded-xl border border-teal-100 px-3 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                                    <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                                    <input id="birth_date" name="birth_date" type="date" max="{{ now()->toDateString() }}" value="{{ old('birth_date') }}" required aria-invalid="{{ $errors->has('birth_date') ? 'true' : 'false' }}" class="min-w-0 w-full border-0 bg-transparent py-2.5 text-sm outline-none">
                                </div>
                            </div>
                            <div>
                                <label for="sex" class="mb-1 block text-xs font-semibold text-slate-700">Sexo <span class="text-teal-700">*</span></label>
                                <select id="sex" name="sex" required aria-invalid="{{ $errors->has('sex') ? 'true' : 'false' }}" class="w-full rounded-xl border border-teal-100 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-teal-600 focus:ring-4 focus:ring-teal-100">
                                    <option value="">Selecciona una opción</option>
                                    <option value="F" @selected(old('sex') === 'F')>Femenino</option>
                                    <option value="M" @selected(old('sex') === 'M')>Masculino</option>
                                    <option value="O" @selected(old('sex') === 'O')>Otro</option>
                                </select>
                            </div>
                            <div>
                                <label for="phone" class="mb-1 block text-xs font-semibold text-slate-700">Teléfono</label>
                                <div class="flex items-center gap-2 rounded-xl border border-teal-100 px-3 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                                    <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 16.5v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 1.1 3.7 2 2 0 0 1 3.1 1.5h3a2 2 0 0 1 2 1.7l.4 2.8a2 2 0 0 1-.6 1.7L6.6 9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 1.7-.6l2.8.4a2 2 0 0 1 1.6 2Z"/></svg>
                                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="Ej. 76543210" autocomplete="tel" class="min-w-0 w-full border-0 bg-transparent py-2.5 text-sm outline-none placeholder:text-slate-400">
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="address" class="mb-1 block text-xs font-semibold text-slate-700">Dirección</label>
                                <div class="flex items-center gap-2 rounded-xl border border-teal-100 px-3 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                                    <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                    <input id="address" name="address" value="{{ old('address') }}" placeholder="Ej. Av. Principal #123" autocomplete="street-address" class="min-w-0 w-full border-0 bg-transparent py-2.5 text-sm outline-none placeholder:text-slate-400">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="mb-2 text-xs font-bold uppercase tracking-[0.15em] text-slate-500">Seguridad de la cuenta</legend>
                        <div class="grid gap-3 sm:grid-cols-2 lg:gap-y-1">
                            <div>
                                <label for="password" class="mb-1 block text-xs font-semibold text-slate-700">Contraseña <span class="text-teal-700">*</span></label>
                                <div class="flex items-center gap-2 rounded-xl border border-teal-100 px-3 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                                    <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg>
                                    <input id="password" name="password" type="password" placeholder="Mínimo 12 caracteres" required minlength="12" autocomplete="new-password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" class="min-w-0 w-full border-0 bg-transparent py-2.5 text-sm outline-none placeholder:text-slate-400">
                                </div>
                            </div>
                            <div>
                                <label for="password_confirmation" class="mb-1 block text-xs font-semibold text-slate-700">Confirmar contraseña <span class="text-teal-700">*</span></label>
                                <div class="flex items-center gap-2 rounded-xl border border-teal-100 px-3 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                                    <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg>
                                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repite la contraseña" required minlength="12" autocomplete="new-password" class="min-w-0 w-full border-0 bg-transparent py-2.5 text-sm outline-none placeholder:text-slate-400">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="flex items-start gap-2 rounded-xl border border-sky-100 bg-sky-50 px-3 py-2.5 text-xs leading-5 text-teal-900">
                        <svg viewBox="0 0 24 24" class="mt-0.5 size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 11v5m0-8h.01"/></svg>
                        Al registrarte, podrás acceder a los servicios para pacientes y mantener tu información actualizada.
                    </div>

                    <div class="grid gap-3 pt-1 sm:grid-cols-2">
                        <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 rounded-xl border border-teal-200 px-4 py-3 text-sm font-semibold text-teal-800 transition hover:bg-teal-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                            Volver a iniciar sesión
                        </a>
                        <button type="submit" class="flex items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                            Registrarme
                        </button>
                    </div>
                </form>

                <p class="mt-5 border-t border-slate-100 pt-4 text-center text-xs text-slate-500">¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="font-bold text-teal-800 hover:underline">Inicia sesión</a></p>
            </div>
        </section>
    </div>
</div>
@endsection
