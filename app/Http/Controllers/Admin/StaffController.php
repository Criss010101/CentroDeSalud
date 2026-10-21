<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = StaffProfile::query()
            ->with(['user.role', 'specialties'])
            ->orderBy('employee_number')
            ->paginate(15);

        return view('admin.staff.index', compact('staff'));
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'staffProfile' => new StaffProfile,
            'roles' => $this->staffRoles(),
            'specialties' => Specialty::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $role = Role::query()->findOrFail($data['role_id']);
        $this->ensureDoctorHasSpecialty($role->code, $data['specialty_ids'] ?? []);

        DB::transaction(function () use ($data, $role): void {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role_id' => $role->id,
            ]);

            $staffProfile = $user->staffProfile()->create($this->profileData($data));

            if ($role->code === 'doctor') {
                $staffProfile->specialties()->sync($data['specialty_ids']);
            }
        });

        return redirect()->route('admin.staff.index')->with('status', 'El personal fue registrado.');
    }

    public function edit(StaffProfile $staff): View
    {
        $staff->load(['user', 'specialties']);

        return view('admin.staff.edit', [
            'staffProfile' => $staff,
            'roles' => $this->staffRoles($staff),
            'specialties' => Specialty::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, StaffProfile $staff): RedirectResponse
    {
        $data = $request->validate($this->rules($staff));
        $role = Role::query()->findOrFail($data['role_id']);
        $this->ensureDoctorHasSpecialty($role->code, $data['specialty_ids'] ?? []);

        if ($staff->user->hasRole('doctor') && $role->code !== 'doctor' && $staff->appointments()->exists()) {
            return back()->withErrors([
                'role_id' => 'No se puede cambiar el tipo de personal porque el médico tiene citas registradas.',
            ])->withInput();
        }

        if (
            $staff->user->hasRole('staff', 'receptionist')
            && ! in_array($role->code, ['staff', 'receptionist'], true)
            && $staff->user->receivedCareEncounters()->exists()
        ) {
            return back()->withErrors([
                'role_id' => 'No se puede cambiar el rol del personal de admisión porque tiene atenciones registradas.',
            ])->withInput();
        }

        DB::transaction(function () use ($data, $role, $staff): void {
            $staff->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => $role->id,
            ]);

            if (filled($data['password'] ?? null)) {
                $staff->user->update(['password' => $data['password']]);
            }

            $staff->update($this->profileData($data));
            $staff->specialties()->sync($role->code === 'doctor' ? ($data['specialty_ids'] ?? []) : []);

            if ($role->code !== 'doctor') {
                $staff->schedules()->delete();
            }
        });

        return redirect()->route('admin.staff.index')->with('status', 'Los datos del personal fueron actualizados.');
    }

    public function destroy(StaffProfile $staff): RedirectResponse
    {
        if ($staff->appointments()->exists() || $staff->user->receivedCareEncounters()->exists()) {
            return back()->with('error', 'No se puede eliminar personal con citas o atenciones registradas.');
        }

        $staff->user()->delete();

        return redirect()->route('admin.staff.index')->with('status', 'El registro del personal fue eliminado.');
    }

    /**
     * @return array<int, Role>
     */
    private function staffRoles(?StaffProfile $staff = null): array
    {
        $roleCodes = ['doctor', 'nurse', 'staff'];

        if ($staff?->user?->hasRole('receptionist')) {
            $roleCodes[] = 'receptionist';
        }

        return Role::query()
            ->whereIn('code', $roleCodes)
            ->orderBy('name')
            ->get()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?StaffProfile $staff = null): array
    {
        $roleCodes = ['doctor', 'nurse', 'staff'];

        if ($staff?->user?->hasRole('receptionist')) {
            $roleCodes[] = 'receptionist';
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff?->user_id)],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->whereIn('code', $roleCodes)),
            ],
            'password' => [$staff ? 'nullable' : 'required', 'string', 'min:12', 'confirmed'],
            'employee_number' => ['required', 'string', 'max:40', Rule::unique('staff_profiles', 'employee_number')->ignore($staff)],
            'position' => ['required', 'string', 'max:100'],
            'hire_date' => ['nullable', 'date', 'before_or_equal:today'],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialty_ids' => ['nullable', 'array'],
            'specialty_ids.*' => [
                'integer',
                Rule::exists('specialties', 'id')->where('is_active', true),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function profileData(array $data): array
    {
        return [
            'employee_number' => $data['employee_number'],
            'position' => $data['position'],
            'hire_date' => $data['hire_date'] ?? null,
            'phone' => $data['phone'] ?? null,
        ];
    }

    /**
     * @param  array<int, int|string>  $specialtyIds
     */
    private function ensureDoctorHasSpecialty(string $roleCode, array $specialtyIds): void
    {
        if ($roleCode === 'doctor' && $specialtyIds === []) {
            throw ValidationException::withMessages([
                'specialty_ids' => 'Selecciona al menos una especialidad para el médico.',
            ]);
        }
    }
}
