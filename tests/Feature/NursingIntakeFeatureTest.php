<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\NursingIntake;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NursingIntakeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_nurse_can_register_initial_measurements_and_responsible_doctor_can_review_them(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $nurse = $this->makeStaffUser('nurse', 'ENF-001');
        $doctor = $this->makeStaffUser('doctor', 'MED-001');
        $patient = $this->makeUser('patient', ['name' => 'Paciente Protegido']);
        $appointment = $this->makeAppointment($doctor, $patient);
        $this->checkInAppointment($appointment);

        $this->actingAs($nurse)
            ->get(route('nursing.intakes.index'))
            ->assertOk()
            ->assertSeeText('Paciente Protegido')
            ->assertSeeText($doctor->name);

        $this->actingAs($nurse)
            ->post(route('nursing.intakes.store', $appointment), [
                'reason' => 'Dolor abdominal referido por el paciente',
                'blood_pressure_systolic' => 120,
                'blood_pressure_diastolic' => 80,
                'heart_rate' => 72,
                'temperature_celsius' => 36.5,
                'respiratory_rate' => 16,
                'oxygen_saturation' => 98,
                'weight_kg' => 65.25,
                'height_cm' => 168,
                'observations' => 'Paciente en recepción',
                'diagnosis' => 'Campo no autorizado',
            ])
            ->assertRedirectToRoute('nursing.intakes.index')
            ->assertSessionHas('status');

        $intake = NursingIntake::query()->firstOrFail();
        $this->assertSame($appointment->id, $intake->appointment_id);
        $this->assertSame($patient->id, $intake->patient_id);
        $this->assertSame($nurse->id, $intake->nurse_user_id);
        $this->assertSame('120', (string) $intake->blood_pressure_systolic);
        $this->assertSame('36.5', (string) $intake->temperature_celsius);
        $this->assertNotNull($intake->measured_at);
        $this->assertDatabaseMissing('nursing_intakes', ['diagnosis' => 'Campo no autorizado']);

        $this->actingAs($doctor)
            ->get(route('nursing.intakes.doctor-show', $appointment))
            ->assertOk()
            ->assertSeeText('Paciente Protegido')
            ->assertSeeText('120 / 80 mmHg')
            ->assertSeeText('Dolor abdominal referido por el paciente')
            ->assertSeeText('no constituyen un diagnóstico médico');
    }

    public function test_nurse_queue_contains_only_active_appointments_for_today(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $nurse = $this->makeStaffUser('nurse', 'ENF-002');
        $doctor = $this->makeStaffUser('doctor', 'MED-002');
        $todayPatient = $this->makeUser('patient', ['name' => 'Paciente de hoy']);
        $futurePatient = $this->makeUser('patient', ['name' => 'Paciente futuro']);
        $cancelledPatient = $this->makeUser('patient', ['name' => 'Paciente cancelado']);

        $todayAppointment = $this->makeAppointment($doctor, $todayPatient);
        $this->checkInAppointment($todayAppointment);
        $this->makeAppointment($doctor, $futurePatient, today()->addDay()->toDateString());
        $this->makeAppointment($doctor, $cancelledPatient, today()->toDateString(), 'cancelled');

        $this->actingAs($nurse)
            ->get(route('nursing.intakes.index'))
            ->assertOk()
            ->assertSeeText('Paciente de hoy')
            ->assertDontSeeText('Paciente futuro')
            ->assertDontSeeText('Paciente cancelado');
    }

    public function test_patient_and_another_doctor_cannot_view_a_nursing_intake(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $nurse = $this->makeStaffUser('nurse', 'ENF-003');
        $doctor = $this->makeStaffUser('doctor', 'MED-003');
        $otherDoctor = $this->makeStaffUser('doctor', 'MED-004');
        $patient = $this->makeUser('patient');
        $appointment = $this->makeAppointment($doctor, $patient);
        $this->checkInAppointment($appointment);

        $this->actingAs($nurse)->post(route('nursing.intakes.store', $appointment), [
            'reason' => 'Control general',
        ])->assertRedirectToRoute('nursing.intakes.index');

        $this->actingAs($otherDoctor)
            ->get(route('nursing.intakes.doctor-show', $appointment))
            ->assertNotFound();

        $this->actingAs($patient)
            ->get(route('nursing.intakes.doctor-show', $appointment))
            ->assertForbidden();
    }

    public function test_only_nursing_staff_can_open_or_save_initial_intakes(): void
    {
        $patient = $this->makeUser('patient');
        $doctor = $this->makeStaffUser('doctor', 'MED-005');
        $appointment = $this->makeAppointment($doctor, $patient);

        $this->actingAs($doctor)->get(route('nursing.intakes.index'))->assertForbidden();
        $this->actingAs($doctor)->get(route('nursing.intakes.edit', $appointment))->assertForbidden();
        $this->actingAs($doctor)->post(route('nursing.intakes.store', $appointment), [
            'reason' => 'No autorizado',
        ])->assertForbidden();
    }

    public function test_nurse_dashboard_only_links_to_available_nursing_workflows(): void
    {
        $nurse = $this->makeStaffUser('nurse', 'ENF-DASHBOARD');

        $this->actingAs($nurse)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Panel de enfermería')
            ->assertSeeText('Recepción y signos vitales')
            ->assertSee(route('nursing.intakes.index'), false)
            ->assertDontSee(route('appointments.availability'), false)
            ->assertDontSee(route('appointments.mine'), false);
    }

    public function test_blood_pressure_requires_both_values_and_out_of_range_measurements_are_rejected(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $nurse = $this->makeStaffUser('nurse', 'ENF-004');
        $doctor = $this->makeStaffUser('doctor', 'MED-006');
        $patient = $this->makeUser('patient');
        $appointment = $this->makeAppointment($doctor, $patient);
        $this->checkInAppointment($appointment);

        $this->actingAs($nurse)
            ->from(route('nursing.intakes.edit', $appointment))
            ->post(route('nursing.intakes.store', $appointment), [
                'reason' => 'Motivo referido',
                'blood_pressure_systolic' => 120,
            ])
            ->assertRedirect(route('nursing.intakes.edit', $appointment))
            ->assertSessionHasErrors('blood_pressure_diastolic');

        $this->actingAs($nurse)
            ->from(route('nursing.intakes.edit', $appointment))
            ->post(route('nursing.intakes.store', $appointment), [
                'reason' => 'Motivo referido',
                'heart_rate' => 301,
                'oxygen_saturation' => 101,
            ])
            ->assertRedirect(route('nursing.intakes.edit', $appointment))
            ->assertSessionHasErrors(['heart_rate', 'oxygen_saturation']);

        $this->assertDatabaseCount('nursing_intakes', 0);
    }

    public function test_nursing_corrections_are_append_only_and_visible_to_the_assigned_doctor(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $nurse = $this->makeStaffUser('nurse', 'ENF-005');
        $secondNurse = $this->makeStaffUser('nurse', 'ENF-006');
        $doctor = $this->makeStaffUser('doctor', 'MED-007');
        $patient = $this->makeUser('patient');
        $appointment = $this->makeAppointment($doctor, $patient);
        $this->checkInAppointment($appointment);

        $this->actingAs($nurse)->post(route('nursing.intakes.store', $appointment), [
            'reason' => 'Cefalea referida',
            'heart_rate' => 70,
        ])->assertRedirectToRoute('nursing.intakes.index');

        $intake = NursingIntake::query()->firstOrFail();

        $this->actingAs($secondNurse)
            ->post(route('nursing.intakes.amendments.store', $intake), [
                'reason' => 'Cefalea referida',
                'heart_rate' => 74,
                'correction_reason' => 'Se corrige transcripción tras verificar la medición.',
            ])
            ->assertRedirect(route('nursing.intakes.edit', $appointment));

        $this->assertDatabaseHas('nursing_intakes', [
            'id' => $intake->id,
            'heart_rate' => 70,
        ]);
        $this->assertDatabaseHas('nursing_intake_amendments', [
            'nursing_intake_id' => $intake->id,
            'author_user_id' => $secondNurse->id,
            'reason' => 'Se corrige transcripción tras verificar la medición.',
        ]);

        $this->actingAs($doctor)
            ->get(route('nursing.intakes.doctor-show', $appointment))
            ->assertOk()
            ->assertSeeText('74 latidos/min')
            ->assertSeeText('70 latidos/min')
            ->assertSeeText('Se corrige transcripción tras verificar la medición.')
            ->assertSeeText($secondNurse->name);
    }

    public function test_intake_cannot_be_overwritten_by_a_second_initial_submission(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $nurse = $this->makeStaffUser('nurse', 'ENF-007');
        $doctor = $this->makeStaffUser('doctor', 'MED-008');
        $patient = $this->makeUser('patient');
        $appointment = $this->makeAppointment($doctor, $patient);
        $this->checkInAppointment($appointment);

        $this->actingAs($nurse)->post(route('nursing.intakes.store', $appointment), [
            'reason' => 'Motivo inicial',
        ])->assertRedirectToRoute('nursing.intakes.index');

        $this->actingAs($nurse)
            ->from(route('nursing.intakes.edit', $appointment))
            ->post(route('nursing.intakes.store', $appointment), [
                'reason' => 'Intento de reemplazo',
            ])
            ->assertRedirect(route('nursing.intakes.edit', $appointment))
            ->assertSessionHasErrors('appointment');

        $this->assertDatabaseCount('nursing_intakes', 1);
        $this->assertDatabaseHas('nursing_intakes', [
            'appointment_id' => $appointment->id,
            'reason' => 'Motivo inicial',
        ]);
    }

    public function test_nurse_and_doctor_views_escape_patient_reported_content(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $nurse = $this->makeStaffUser('nurse', 'ENF-008');
        $doctor = $this->makeStaffUser('doctor', 'MED-009');
        $patient = $this->makeUser('patient');
        $appointment = $this->makeAppointment($doctor, $patient);
        $this->checkInAppointment($appointment);
        $unsafeText = '<script>alert("x")</script>';

        $this->actingAs($nurse)->post(route('nursing.intakes.store', $appointment), [
            'reason' => $unsafeText,
            'observations' => $unsafeText,
        ])->assertRedirectToRoute('nursing.intakes.index');

        $this->actingAs($doctor)
            ->get(route('nursing.intakes.doctor-show', $appointment))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false)
            ->assertDontSee($unsafeText, false);
    }

    private function makeUser(string $role, array $attributes = []): User
    {
        return User::factory()->create([
            'role_id' => $this->role($role)->id,
            ...$attributes,
        ]);
    }

    private function makeStaffUser(string $role, string $employeeNumber): User
    {
        $user = $this->makeUser($role);
        $user->staffProfile()->create([
            'employee_number' => $employeeNumber,
            'position' => $role === 'nurse' ? 'Enfermería' : 'Médico',
        ]);

        return $user->load('staffProfile');
    }

    private function makeAppointment(User $doctor, User $patient, ?string $date = null, string $status = 'confirmed'): Appointment
    {
        return Appointment::query()->create([
            'staff_profile_id' => $doctor->staffProfile->id,
            'patient_id' => $patient->id,
            'appointment_date' => $date ?? today()->toDateString(),
            'start_time' => '09:00',
            'status' => $status,
        ]);
    }

    private function checkInAppointment(Appointment $appointment): void
    {
        $patient = $appointment->patient;
        $patient->patientProfile()->create([
            'name' => $patient->name,
            'national_id' => 'TEST-'.$patient->id,
            'birth_date' => '1995-06-12',
            'sex' => 'F',
        ]);
        $admissionStaff = $this->makeStaffUser('staff', 'ADM-'.$appointment->id);

        $this->actingAs($admissionStaff)
            ->post(route('reception.appointments.check-in', $appointment))
            ->assertRedirectToRoute('reception.index');
    }

    private function role(string $code): Role
    {
        $names = [
            'admin' => 'Administrador',
            'doctor' => 'Médico',
            'nurse' => 'Enfermería',
            'receptionist' => 'Recepción',
            'staff' => 'Personal administrativo',
            'patient' => 'Paciente',
        ];

        return Role::query()->firstOrCreate(['code' => $code], ['name' => $names[$code]]);
    }
}
