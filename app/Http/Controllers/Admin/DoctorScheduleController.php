<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DoctorScheduleController extends Controller
{
    private const DAYS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public function edit(StaffProfile $staff): View
    {
        abort_unless($staff->user->hasRole('doctor'), 404);

        return view('admin.schedules.edit', [
            'staffProfile' => $staff->load('user', 'schedules'),
            'days' => self::DAYS,
            'schedules' => $staff->schedules->keyBy('day_of_week'),
        ]);
    }

    public function update(Request $request, StaffProfile $staff): RedirectResponse
    {
        abort_unless($staff->user->hasRole('doctor'), 404);

        $data = $request->validate([
            'schedules' => ['required', 'array:1,2,3,4,5,6,7'],
            'schedules.*.enabled' => ['sometimes', 'boolean'],
            'schedules.*.start_time' => ['nullable', 'date_format:H:i'],
            'schedules.*.end_time' => ['nullable', 'date_format:H:i'],
        ]);

        $errors = [];

        foreach (self::DAYS as $day => $label) {
            if (! $request->boolean("schedules.$day.enabled")) {
                continue;
            }

            $schedule = $data['schedules'][$day] ?? [];

            if (blank($schedule['start_time'] ?? null) || blank($schedule['end_time'] ?? null)) {
                $errors["schedules.$day.start_time"] = "Completa el horario de inicio y fin para $label.";
            } elseif ($schedule['start_time'] >= $schedule['end_time']) {
                $errors["schedules.$day.end_time"] = "La hora de fin debe ser posterior a la hora de inicio para $label.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($data, $request, $staff): void {
            foreach (array_keys(self::DAYS) as $day) {
                if (! $request->boolean("schedules.$day.enabled")) {
                    $staff->schedules()->where('day_of_week', $day)->delete();

                    continue;
                }

                $schedule = $data['schedules'][$day];
                $staff->schedules()->updateOrCreate(
                    ['day_of_week' => $day],
                    [
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                    ],
                );
            }
        });

        return redirect()->route('admin.staff.index')->with('status', 'La disponibilidad semanal fue actualizada.');
    }
}
