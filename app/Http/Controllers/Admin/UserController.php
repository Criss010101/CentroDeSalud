<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->with(['role', 'patientProfile'])->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::query()->orderBy('name')->get();
        $user = new User;

        return view('admin.users.create', compact('roles', 'user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules($request));
        $role = Role::query()->findOrFail($data['role_id']);

        DB::transaction(function () use ($data, $role): void {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role_id' => $role->id,
            ]);

            if ($role->code === 'patient') {
                $user->patientProfile()->create($this->profileData($data));
            }
        });

        return redirect()->route('admin.users.index')->with('status', 'La cuenta fue creada.');
    }

    public function edit(User $usuario): View
    {
        $usuario->load('patientProfile');
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.edit', ['user' => $usuario, 'roles' => $roles]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $data = $request->validate($this->rules($request, $usuario));

        if ($usuario->is($request->user()) && (int) $data['role_id'] !== (int) $usuario->role_id) {
            return back()->withErrors(['role_id' => 'No puedes cambiar tu propio rol de administrador.'])->withInput();
        }

        $role = Role::query()->findOrFail($data['role_id']);

        DB::transaction(function () use ($data, $role, $usuario): void {
            $usuario->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => $role->id,
            ]);

            if (filled($data['password'] ?? null)) {
                $usuario->update(['password' => $data['password']]);
            }

            if ($role->code === 'patient') {
                $usuario->patientProfile()->updateOrCreate([], $this->profileData($data));
            }
        });

        return redirect()->route('admin.users.index')->with('status', 'La cuenta fue actualizada.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes eliminar la cuenta con la que iniciaste sesión.');
        }

        if ($usuario->hasRole('admin') && User::query()->where('role_id', $usuario->role_id)->count() <= 1) {
            return back()->with('error', 'Debe existir al menos un administrador en el sistema.');
        }

        $usuario->delete();

        return redirect()->route('admin.users.index')->with('status', 'La cuenta fue eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request, ?User $user = null): array
    {
        $isPatient = (string) $request->input('role_id') === (string) Role::query()->where('code', 'patient')->value('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:12', 'confirmed'],
            'national_id' => [Rule::requiredIf($isPatient), 'nullable', 'string', 'max:30', Rule::unique('patient_profiles', 'national_id')->ignore($user?->patientProfile?->id)],
            'birth_date' => [Rule::requiredIf($isPatient), 'nullable', 'date', 'before_or_equal:today'],
            'sex' => [Rule::requiredIf($isPatient), 'nullable', 'in:F,M,O'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function profileData(array $data): array
    {
        return [
            'national_id' => $data['national_id'],
            'birth_date' => $data['birth_date'],
            'sex' => $data['sex'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ];
    }
}
