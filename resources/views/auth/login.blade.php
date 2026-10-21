@extends('layouts.app')

@section('title', 'Ingresar')

@section('content')
<div class="flex w-full items-center justify-center p-3 sm:p-6 lg:h-full lg:min-h-0 lg:p-0">
    <div class="grid w-full max-w-6xl overflow-hidden rounded-3xl border border-white/50 bg-white/10 shadow-2xl shadow-teal-950/20 lg:h-full lg:max-w-[1600px] lg:min-h-0 lg:grid-cols-[0.92fr_1.08fr]">
        @include('auth._brand-panel')

        <section class="flex items-center justify-center bg-white p-5 sm:p-8 lg:min-h-0 lg:p-10">
            <div class="w-full max-w-md">
                <div class="mb-7">
                    <div class="mb-5 grid size-12 place-items-center rounded-2xl bg-teal-50 text-teal-800">
                        <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M19 10V7a7 7 0 0 0-14 0v3m-1 0h16v11H4V10Z"/></svg>
                    </div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-700">Portal de atención</p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Iniciar sesión</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Ingresa tus credenciales para acceder al sistema del Centro de Salud San Juan.</p>
                </div>

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-semibold">No se pudo iniciar sesión.</p>
                        <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-700">Correo electrónico</label>
                        <div class="flex items-center gap-3 rounded-xl border border-teal-100 bg-white px-4 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                            <svg viewBox="0 0 24 24" class="size-5 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="ejemplo@correo.com" required autocomplete="username" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="min-w-0 w-full border-0 bg-transparent py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400">
                        </div>
                    </div>
                    <div>
                        <label for="password" class="mb-1.5 block text-xs font-semibold text-slate-700">Contraseña</label>
                        <div class="flex items-center gap-3 rounded-xl border border-teal-100 bg-white px-4 transition focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-100">
                            <svg viewBox="0 0 24 24" class="size-5 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 4v3"/></svg>
                            <input id="password" name="password" type="password" placeholder="Ingresa tu contraseña" required autocomplete="current-password" class="min-w-0 w-full border-0 bg-transparent py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                        <label class="flex items-center gap-2 text-xs font-medium text-slate-600">
                            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                            Mantener mi sesión
                        </label>
                    </div>

                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-700 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3m9-9h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/></svg>
                        Ingresar
                    </button>
                </form>

                <div class="my-6 flex items-center gap-3 text-xs text-slate-400">
                    <span class="h-px flex-1 bg-teal-100"></span>
                    ¿Eres paciente nuevo?
                    <span class="h-px flex-1 bg-teal-100"></span>
                </div>
                <a href="{{ route('register') }}" class="flex w-full items-center justify-center gap-2 rounded-xl border border-teal-200 px-5 py-3 text-sm font-bold text-teal-800 transition hover:border-teal-400 hover:bg-teal-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10-1v6m-3-3h6"/></svg>
                    Crea tu cuenta de paciente
                </a>

                <div class="mt-6 border-t border-slate-100 pt-5">
                    <p class="text-center text-[11px] leading-5 text-slate-500">El acceso se determina por el rol asignado a tu cuenta.</p>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div class="flex flex-col items-center gap-1 rounded-xl bg-teal-50/70 px-2 py-3 text-center text-[11px] font-semibold text-slate-600">
                            <svg viewBox="0 0 24 24" class="size-5 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                            Paciente
                        </div>
                        <div class="flex flex-col items-center gap-1 rounded-xl bg-teal-50/70 px-2 py-3 text-center text-[11px] font-semibold text-slate-600">
                            <svg viewBox="0 0 24 24" class="size-5 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M8 21h8m-4-4v4m-7-9a7 7 0 0 1 14 0v2a2 2 0 0 1-2 2H9a4 4 0 0 1-4-4v-1Zm7-8v4m-2-2h4"/></svg>
                            Médico
                        </div>
                        <div class="flex flex-col items-center gap-1 rounded-xl bg-teal-50/70 px-2 py-3 text-center text-[11px] font-semibold text-slate-600">
                            <svg viewBox="0 0 24 24" class="size-5 text-teal-700" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2A1.7 1.7 0 0 0 5.4 5.8l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"/></svg>
                            Administrador
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
