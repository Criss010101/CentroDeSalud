<section class="auth-brand-panel relative isolate flex min-h-64 flex-col justify-between overflow-hidden p-6 text-white sm:p-8 lg:min-h-full lg:p-10">
    <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-20 -top-24 size-80 rounded-full bg-sky-200/25 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-20 size-72 rounded-full bg-cyan-300/20 blur-3xl"></div>
        <div class="absolute inset-x-0 bottom-0 h-2/5 bg-gradient-to-t from-teal-950/75 to-transparent"></div>
    </div>

    <a href="{{ route('login') }}" class="relative z-10 flex w-fit items-center gap-3 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white text-teal-800 shadow-lg shadow-teal-950/15">
            <svg viewBox="0 0 48 48" class="size-9" fill="none" aria-hidden="true">
                <path d="M24 40S7 30.5 7 18.5C7 12.7 11.1 9 16.1 9c3.4 0 6.2 2 7.9 5 1.7-3 4.5-5 7.9-5C36.9 9 41 12.7 41 18.5 41 30.5 24 40 24 40Z" fill="currentColor" opacity=".13"/>
                <path d="M24 37S8.5 28.4 8.5 18.7c0-5.1 3.4-8.2 7.8-8.2 3.4 0 6.2 2.2 7.7 5.8 1.5-3.6 4.3-5.8 7.7-5.8 4.4 0 7.8 3.1 7.8 8.2C39.5 28.4 24 37 24 37Z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
                <path d="M10 23h8l3-7 5 16 4-9 2 3h6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="leading-tight">
            <span class="block text-sm font-semibold tracking-wide text-teal-50">Centro de Salud</span>
            <span class="block text-2xl font-extrabold tracking-tight">San Juan</span>
            <span class="mt-1 block text-xs italic text-teal-100">Tu salud, nuestra prioridad</span>
        </span>
    </a>

    <div class="relative z-10 mt-8 flex flex-1 flex-col justify-end lg:mt-4">
        <div class="max-w-md">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-sky-100">Cerca de ti, cuidando de ti</p>
            <h1 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Bienvenido al Centro de Salud San Juan</h1>
            <p class="mt-4 max-w-sm text-sm leading-6 text-teal-50/90 sm:text-base">Accede de forma segura a tu cuenta y a la información del centro.</p>
        </div>
    </div>

    <div class="relative z-10 mt-6">
        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-teal-100">Atención para toda la comunidad</p>
        <div class="grid grid-cols-4 gap-2 text-center text-[11px] font-medium text-white sm:gap-3 sm:text-xs">
            <div class="rounded-xl border border-white/15 bg-white/10 px-2 py-3 backdrop-blur-sm">
                <svg viewBox="0 0 24 24" class="mx-auto mb-1.5 size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                Pacientes
            </div>
            <div class="rounded-xl border border-white/15 bg-white/10 px-2 py-3 backdrop-blur-sm">
                <svg viewBox="0 0 24 24" class="mx-auto mb-1.5 size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M8 21h8m-4-4v4m-7-9a7 7 0 0 1 14 0v2a2 2 0 0 1-2 2H9a4 4 0 0 1-4-4v-1Zm7-8v4m-2-2h4"/></svg>
                Médicos
            </div>
            <div class="rounded-xl border border-white/15 bg-white/10 px-2 py-3 backdrop-blur-sm">
                <svg viewBox="0 0 24 24" class="mx-auto mb-1.5 size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18m-9 3v4m-2-2h4"/></svg>
                Citas
            </div>
            <div class="rounded-xl border border-white/15 bg-white/10 px-2 py-3 backdrop-blur-sm">
                <svg viewBox="0 0 24 24" class="mx-auto mb-1.5 size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3h10l4 4v14H3V3h4Zm9 0v5h5M7 12h10M7 16h10M7 8h3"/></svg>
                Historias
            </div>
        </div>
    </div>
</section>
