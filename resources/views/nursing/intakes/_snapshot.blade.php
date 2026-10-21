<div class="rounded-xl border border-slate-200 p-4">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
        <span class="text-xs text-slate-500">{{ $author }} · {{ $recordedAt }}</span>
    </div>
    @if ($amendmentReason)
        <p class="mt-2 text-sm text-slate-600">Motivo de rectificación: {{ $amendmentReason }}</p>
    @endif
    <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
        <div class="sm:col-span-2"><dt class="font-medium text-slate-500">Motivo referido</dt><dd class="mt-1 whitespace-pre-wrap">{{ $snapshot['reason'] ?? '—' }}</dd></div>
        <div><dt class="font-medium text-slate-500">Presión arterial</dt><dd class="mt-1">{{ ($snapshot['blood_pressure_systolic'] ?? null) !== null ? $snapshot['blood_pressure_systolic'].' / '.$snapshot['blood_pressure_diastolic'].' mmHg' : 'No registrada' }}</dd></div>
        <div><dt class="font-medium text-slate-500">Frecuencia cardíaca</dt><dd class="mt-1">{{ ($snapshot['heart_rate'] ?? null) !== null ? $snapshot['heart_rate'].' latidos/min' : 'No registrada' }}</dd></div>
        <div><dt class="font-medium text-slate-500">Temperatura</dt><dd class="mt-1">{{ ($snapshot['temperature_celsius'] ?? null) !== null ? $snapshot['temperature_celsius'].' °C' : 'No registrada' }}</dd></div>
        <div><dt class="font-medium text-slate-500">Frecuencia respiratoria</dt><dd class="mt-1">{{ ($snapshot['respiratory_rate'] ?? null) !== null ? $snapshot['respiratory_rate'].' respiraciones/min' : 'No registrada' }}</dd></div>
        <div><dt class="font-medium text-slate-500">Saturación de oxígeno</dt><dd class="mt-1">{{ ($snapshot['oxygen_saturation'] ?? null) !== null ? $snapshot['oxygen_saturation'].' %' : 'No registrada' }}</dd></div>
        <div><dt class="font-medium text-slate-500">Peso</dt><dd class="mt-1">{{ ($snapshot['weight_kg'] ?? null) !== null ? $snapshot['weight_kg'].' kg' : 'No registrado' }}</dd></div>
        <div><dt class="font-medium text-slate-500">Talla</dt><dd class="mt-1">{{ ($snapshot['height_cm'] ?? null) !== null ? $snapshot['height_cm'].' cm' : 'No registrada' }}</dd></div>
        <div class="sm:col-span-2"><dt class="font-medium text-slate-500">Observaciones de enfermería</dt><dd class="mt-1 whitespace-pre-wrap">{{ $snapshot['observations'] ?: 'Sin observaciones' }}</dd></div>
    </dl>
</div>
