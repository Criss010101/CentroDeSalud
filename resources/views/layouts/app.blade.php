<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Centro de Salud San Juan') · San Juan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ request()->routeIs('login', 'register') ? 'auth-background min-h-screen text-slate-800 antialiased' : 'min-h-screen bg-slate-100 text-slate-800 antialiased' }}">
    @if (request()->routeIs('login', 'register'))
        <main class="auth-main">
            @yield('content')
        </main>
    @else
        <div class="min-h-screen bg-slate-100">
            <header class="flex min-h-16 items-center justify-between gap-4 bg-gradient-to-r from-slate-900 to-slate-800 px-4 text-white shadow-md sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                    <span class="grid size-10 place-items-center rounded-xl border border-white/15 bg-white/10 text-white">
                        <svg viewBox="0 0 48 48" class="size-8" fill="none" aria-hidden="true">
                            <path d="M24 40S7 30.5 7 18.5C7 12.7 11.1 9 16.1 9c3.4 0 6.2 2 7.9 5 1.7-3 4.5-5 7.9-5C36.9 9 41 12.7 41 18.5 41 30.5 24 40 24 40Z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
                            <path d="M10 23h8l3-7 5 16 4-9 2 3h6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-xs font-medium text-slate-300">Centro de Salud</span>
                        <span class="block text-lg font-bold tracking-tight">San Juan</span>
                    </span>
                </a>

                @auth
                    <div class="flex items-center gap-3 sm:gap-5">
                        <span class="hidden text-right sm:block">
                            <span class="block text-sm font-semibold">{{ auth()->user()->name }}</span>
                            <span class="block text-xs text-slate-300">{{ auth()->user()->role?->name ?? 'Usuario' }}</span>
                        </span>
                        <span class="grid size-10 place-items-center rounded-full border border-white/20 bg-white/15 text-sm font-bold text-white" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 rounded-lg border border-white/20 px-3 py-2 text-sm font-medium text-white transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3m9-9h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/></svg>
                                <span class="hidden sm:inline">Salir</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-3 text-sm">
                        <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-slate-200 transition hover:bg-white/10">Ingresar</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-teal-700 px-4 py-2 font-medium text-white transition hover:bg-teal-600">Crear cuenta</a>
                    </div>
                @endauth
            </header>

            <div class="grid min-h-[calc(100svh-4rem)] lg:grid-cols-[15rem_minmax(0,1fr)]">
                @auth
                    <aside class="bg-slate-900 text-slate-200 lg:flex lg:flex-col">
                        <nav aria-label="Navegación principal" class="flex gap-2 overflow-x-auto p-3 lg:flex-1 lg:flex-col lg:gap-1 lg:overflow-visible lg:px-3 lg:py-5">
                            <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-teal-700 text-white shadow-sm shadow-teal-950/20 hover:bg-teal-600' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1V10Z"/></svg>
                                Inicio
                            </a>

                            @if (auth()->user()->hasRole('admin', 'doctor'))
                                <a href="{{ route('patients.index') }}" @if (request()->routeIs('patients.index')) aria-current="page" @endif class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('patients.index') ? 'bg-teal-700 text-white shadow-sm shadow-teal-950/20 hover:bg-teal-600' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm5-3h5m-2.5-2.5v5"/></svg>
                                    Pacientes
                                </a>
                            @endif

                            @if (auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.users.index') }}" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-teal-700 text-white shadow-sm shadow-teal-950/20 hover:bg-teal-600' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm5-2h5m-2.5-2.5v5"/></svg>
                                    Usuarios y roles
                                </a>
                            @endif

                            @if (auth()->user()->hasRole('patient'))
                                <a href="{{ route('patients.profile.edit') }}" @if (request()->routeIs('patients.profile.*')) aria-current="page" @endif class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('patients.profile.*') ? 'bg-teal-700 text-white shadow-sm shadow-teal-950/20 hover:bg-teal-600' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 9v-2a7 7 0 0 0-14 0v2m11-8 2 2 4-4"/></svg>
                                    Mi ficha
                                </a>
                            @endif
                        </nav>

                        <div class="hidden border-t border-white/10 p-3 lg:block">
                            <p class="px-4 py-2 text-xs text-slate-400">Sistema de Gestión · Sprint 1</p>
                        </div>
                    </aside>
                @else
                    <aside class="hidden bg-slate-900 lg:block"></aside>
                @endauth

                <main class="min-w-0 p-4 sm:p-6 lg:p-8 xl:p-10">
                    <div class="mx-auto max-w-7xl">
                        @if (session('status'))
                            <div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
                        @endif
                        @if (session('error'))
                            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
                        @endif
                        @if ($errors->any())
                            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                <p class="font-semibold">Revisa los datos ingresados:</p>
                                <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    @endif
</body>
</html>
