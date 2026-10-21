<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="reason" class="mb-2 block text-sm font-medium">Motivo referido por el paciente *</label>
        <textarea id="reason" name="reason" rows="3" required maxlength="500" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('reason', $measurements['reason'] ?? '') }}</textarea>
        <p class="mt-1 text-xs text-slate-500">Registra lo que refiere el paciente; no lo formules como diagnóstico.</p>
    </div>
    <div>
        <label for="blood_pressure_systolic" class="mb-2 block text-sm font-medium">Presión sistólica (mmHg)</label>
        <input id="blood_pressure_systolic" name="blood_pressure_systolic" type="number" min="1" max="300" step="1" value="{{ old('blood_pressure_systolic', $measurements['blood_pressure_systolic'] ?? '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3">
    </div>
    <div>
        <label for="blood_pressure_diastolic" class="mb-2 block text-sm font-medium">Presión diastólica (mmHg)</label>
        <input id="blood_pressure_diastolic" name="blood_pressure_diastolic" type="number" min="1" max="300" step="1" value="{{ old('blood_pressure_diastolic', $measurements['blood_pressure_diastolic'] ?? '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3">
    </div>
    <div>
        <label for="heart_rate" class="mb-2 block text-sm font-medium">Frecuencia cardíaca (latidos/min)</label>
        <input id="heart_rate" name="heart_rate" type="number" min="1" max="300" step="1" value="{{ old('heart_rate', $measurements['heart_rate'] ?? '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3">
    </div>
    <div>
        <label for="temperature_celsius" class="mb-2 block text-sm font-medium">Temperatura (°C)</label>
        <input id="temperature_celsius" name="temperature_celsius" type="number" min="20" max="50" step="0.1" value="{{ old('temperature_celsius', $measurements['temperature_celsius'] ?? '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3">
    </div>
    <div>
        <label for="respiratory_rate" class="mb-2 block text-sm font-medium">Frecuencia respiratoria (respiraciones/min)</label>
        <input id="respiratory_rate" name="respiratory_rate" type="number" min="1" max="100" step="1" value="{{ old('respiratory_rate', $measurements['respiratory_rate'] ?? '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3">
    </div>
    <div>
        <label for="oxygen_saturation" class="mb-2 block text-sm font-medium">Saturación de oxígeno (%)</label>
        <input id="oxygen_saturation" name="oxygen_saturation" type="number" min="0" max="100" step="1" value="{{ old('oxygen_saturation', $measurements['oxygen_saturation'] ?? '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3">
    </div>
    <div>
        <label for="weight_kg" class="mb-2 block text-sm font-medium">Peso (kg)</label>
        <input id="weight_kg" name="weight_kg" type="number" min="0.1" max="500" step="0.01" value="{{ old('weight_kg', $measurements['weight_kg'] ?? '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3">
    </div>
    <div>
        <label for="height_cm" class="mb-2 block text-sm font-medium">Talla (cm)</label>
        <input id="height_cm" name="height_cm" type="number" min="20" max="300" step="0.01" value="{{ old('height_cm', $measurements['height_cm'] ?? '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3">
    </div>
    <div class="sm:col-span-2">
        <label for="observations" class="mb-2 block text-sm font-medium">Observaciones de recepción</label>
        <textarea id="observations" name="observations" rows="3" maxlength="2000" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('observations', $measurements['observations'] ?? '') }}</textarea>
    </div>
</div>
