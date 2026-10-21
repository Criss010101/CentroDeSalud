<div class="grid gap-5">
    <div>
        <label for="name" class="mb-2 block text-sm font-medium">Nombre *</label>
        <input id="name" name="name" value="{{ old('name', $specialty->name) }}" required maxlength="120" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="description" class="mb-2 block text-sm font-medium">Descripción</label>
        <textarea id="description" name="description" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('description', $specialty->description) }}</textarea>
    </div>
    <label class="flex items-center gap-3 text-sm font-medium">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $specialty->exists ? $specialty->is_active : true)) class="size-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
        Disponible para reservas
    </label>
</div>
<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">{{ $specialty->exists ? 'Guardar cambios' : 'Crear especialidad' }}</button>
    <a href="{{ route('admin.specialties.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
</div>
