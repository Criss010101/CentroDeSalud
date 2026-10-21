@extends('layouts.app')

@section('title', 'Disponibilidad médica')

@section('content')
<section class="mx-auto max-w-4xl">
    <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administración</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">Disponibilidad semanal</h1>
    <p class="mt-2 text-slate-600">{{ $staffProfile->user->name }} · Configura los bloques recurrentes en los que acepta citas de 30 minutos.</p>

    <form method="POST" action="{{ route('admin.staff.schedules.update', $staffProfile) }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        @csrf
        @method('PUT')
        <div class="grid gap-4">
            @foreach ($days as $day => $label)
                @php($schedule = $schedules->get($day))
                <fieldset class="grid gap-4 rounded-xl border border-slate-200 p-4 sm:grid-cols-[minmax(8rem,1fr)_1fr_1fr] sm:items-end">
                    <legend class="sr-only">{{ $label }}</legend>
                    <label class="flex items-center gap-3 font-semibold">
                        <input type="checkbox" name="schedules[{{ $day }}][enabled]" value="1" @checked(old("schedules.$day.enabled", $schedule !== null)) class="size-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                        {{ $label }}
                    </label>
                    <div>
                        <label for="start-{{ $day }}" class="mb-2 block text-sm font-medium">Desde</label>
                        <input id="start-{{ $day }}" name="schedules[{{ $day }}][start_time]" type="time" value="{{ old("schedules.$day.start_time", $schedule?->start_time ? substr($schedule->start_time, 0, 5) : '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>
                    <div>
                        <label for="end-{{ $day }}" class="mb-2 block text-sm font-medium">Hasta</label>
                        <input id="end-{{ $day }}" name="schedules[{{ $day }}][end_time]" type="time" value="{{ old("schedules.$day.end_time", $schedule?->end_time ? substr($schedule->end_time, 0, 5) : '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>
                </fieldset>
            @endforeach
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">Guardar disponibilidad</button>
            <a href="{{ route('admin.staff.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Volver al personal</a>
        </div>
    </form>
</section>
@endsection
