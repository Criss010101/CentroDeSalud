<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class PatientProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('patients.profile.edit', [
            'profile' => $request->user()->patientProfile()->firstOrFail(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $request->user()->patientProfile()->firstOrFail();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'national_id' => ['required', 'string', 'max:30', 'unique:patient_profiles,national_id,'.$profile->id],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:F,M,O'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
        $profile->update(Arr::only($data, [
            'national_id',
            'birth_date',
            'sex',
            'phone',
            'address',
        ]));

        return redirect()->route('patients.profile.edit')->with('status', 'Tu ficha fue actualizada.');
    }
}
