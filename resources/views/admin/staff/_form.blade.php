<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-2 block text-sm font-medium">Nombre completo *</label>
        <input id="name" name="name" value="{{ old('name', $staffProfile->user?->name) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="email" class="mb-2 block text-sm font-medium">Correo electrónico *</label>
        <input id="email" name="email" type="email" value="{{ old('email', $staffProfile->user?->email) }}" required autocomplete="email" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="role_id" class="mb-2 block text-sm font-medium">Tipo de personal *</label>
        <select id="role_id" name="role_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
            <option value="">Selecciona un tipo</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" data-code="{{ $role->code }}" @selected((string) old('role_id', $staffProfile->user?->role_id) === (string) $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="employee_number" class="mb-2 block text-sm font-medium">Número laboral *</label>
        <input id="employee_number" name="employee_number" value="{{ old('employee_number', $staffProfile->employee_number) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="position" class="mb-2 block text-sm font-medium">Cargo *</label>
        <input id="position" name="position" value="{{ old('position', $staffProfile->position) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="hire_date" class="mb-2 block text-sm font-medium">Fecha de ingreso</label>
        <input id="hire_date" name="hire_date" type="date" max="{{ today()->toDateString() }}" value="{{ old('hire_date', $staffProfile->hire_date?->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="phone" class="mb-2 block text-sm font-medium">Teléfono</label>
        <input id="phone" name="phone" type="tel" value="{{ old('phone', $staffProfile->phone) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="password" class="mb-2 block text-sm font-medium">Contraseña {{ $staffProfile->exists ? '(opcional, mínimo 12 caracteres)' : '(mínimo 12 caracteres) *' }}</label>
        <input id="password" name="password" type="password" @unless ($staffProfile->exists) required @endunless minlength="12" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="password_confirmation" class="mb-2 block text-sm font-medium">Confirmar contraseña</label>
        <input id="password_confirmation" name="password_confirmation" type="password" @unless ($staffProfile->exists) required @endunless minlength="12" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
</div>

<fieldset class="mt-7 rounded-2xl border border-slate-200 p-5">
    <legend class="px-2 font-semibold text-slate-900">Especialidades médicas</legend>
    <p class="mb-4 text-sm text-slate-500">Selecciona al menos una especialidad cuando registres un médico.</p>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($specialties as $specialty)
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
                <input type="checkbox" name="specialty_ids[]" value="{{ $specialty->id }}" @checked(in_array((string) $specialty->id, array_map('strval', array_filter((array) old('specialty_ids', $staffProfile->specialties->pluck('id')->all()), 'is_scalar')), true)) class="size-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                <span class="text-sm">{{ $specialty->name }}</span>
            </label>
        @endforeach
    </div>
    @if ($specialties->isEmpty())
        <p class="text-sm text-amber-800">Primero debes crear una especialidad para poder registrar médicos.</p>
    @endif
</fieldset>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">{{ $staffProfile->exists ? 'Guardar cambios' : 'Registrar personal' }}</button>
    <a href="{{ route('admin.staff.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
</div>
