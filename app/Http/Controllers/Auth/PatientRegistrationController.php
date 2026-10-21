<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'national_id' => ['required', 'string', 'max:30', 'unique:patient_profiles,national_id'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:F,M,O'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($data): User {
            $patientRole = Role::query()->where('code', 'patient')->firstOrFail();
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role_id' => $patientRole->id,
            ]);

            $user->patientProfile()->create([
                'name' => $data['name'],
                'national_id' => $data['national_id'],
                'birth_date' => $data['birth_date'],
                'sex' => $data['sex'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            return $user;
        });

        auth()->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Tu cuenta fue creada correctamente.');
    }
}
