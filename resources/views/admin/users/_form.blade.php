<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="mb-2 block text-sm font-medium">Nombre completo *</label>
        <input id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="email" class="mb-2 block text-sm font-medium">Correo electrónico *</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="role_id" class="mb-2 block text-sm font-medium">Rol *</label>
        @if ($user->exists && $user->is(auth()->user()))
            <input type="hidden" name="role_id" value="{{ old('role_id', $user->role_id) }}">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">{{ $user->role?->name }} <span class="text-xs text-slate-500">(no puedes cambiar tu propio rol)</span></div>
        @else
            <select id="role_id" name="role_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                <option value="">Selecciona un rol</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" data-code="{{ $role->code }}" @selected((string) old('role_id', $user->role_id) === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
        @endif
    </div>
    <div>
        <label for="password" class="mb-2 block text-sm font-medium">Contraseña {{ $user->exists ? '(dejar en blanco para conservarla)' : '(mínimo 12 caracteres) *' }}</label>
        <input id="password" name="password" type="password" @unless ($user->exists) required @endunless minlength="12" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
    <div>
        <label for="password_confirmation" class="mb-2 block text-sm font-medium">Confirmar contraseña</label>
        <input id="password_confirmation" name="password_confirmation" type="password" @unless ($user->exists) required @endunless minlength="12" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
    </div>
</div>

<fieldset class="mt-8 rounded-2xl border border-slate-200 p-5">
    <legend class="px-2 font-semibold text-slate-900">Ficha del paciente</legend>
    <p class="mb-5 text-sm text-slate-500">Estos datos son obligatorios cuando el rol seleccionado es Paciente.</p>
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="national_id" class="mb-2 block text-sm font-medium">Carnet de identidad</label>
            <input id="national_id" name="national_id" value="{{ old('national_id', $user->patientProfile?->national_id) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <div>
            <label for="birth_date" class="mb-2 block text-sm font-medium">Fecha de nacimiento</label>
            <input id="birth_date" name="birth_date" type="date" max="{{ now()->toDateString() }}" value="{{ old('birth_date', $user->patientProfile?->birth_date?->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <div>
            <label for="sex" class="mb-2 block text-sm font-medium">Sexo</label>
            <select id="sex" name="sex" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                <option value="">Selecciona una opción</option>
                <option value="F" @selected(old('sex', $user->patientProfile?->sex) === 'F')>Femenino</option>
                <option value="M" @selected(old('sex', $user->patientProfile?->sex) === 'M')>Masculino</option>
                <option value="O" @selected(old('sex', $user->patientProfile?->sex) === 'O')>Otro</option>
            </select>
        </div>
        <div>
            <label for="phone" class="mb-2 block text-sm font-medium">Teléfono</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->patientProfile?->phone) }}" autocomplete="tel" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
        <div class="sm:col-span-2">
            <label for="address" class="mb-2 block text-sm font-medium">Dirección</label>
            <input id="address" name="address" value="{{ old('address', $user->patientProfile?->address) }}" autocomplete="street-address" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>
    </div>
</fieldset>

<div class="mt-6 flex flex-wrap items-center gap-3">
    <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">{{ $user->exists ? 'Guardar cambios' : 'Crear usuario' }}</button>
    <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
</div>
