<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\MedicalConsultation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintThreeMedicalRecordsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_assigned_doctor_can_record_consultation_and_view_it_in_patient_history(): void
    {
        $this->travelTo('2026-10-20 10:00:00');
        $doctor = $this->makeStaffUser('doctor', 'MED-101');
        $patient = $this->makeUser('patient', ['name' => 'Paciente Historia']);
        $appointment = $this->makeAppointment($doctor, $patient, '2026-10-20', 'confirmed');
        $this->checkInAppointment($appointment);

        $this->actingAs($doctor)
            ->get(route('medical-consultations.create', $appointment))
            ->assertOk()
            ->assertSeeText('Registrar consulta');

        $this->actingAs($doctor)
            ->post(route('medical-consultations.store', $appointment), [
                'reason' => 'Dolor referido',
                'observations' => '<script>alert("x")</script>',
                'diagnosis' => 'Diagnóstico profesional',
                'treatment' => 'Tratamiento indicado',
                'recommendations' => 'Control posterior',
                'nurse_user_id' => 99999,
            ])
            ->assertRedirectToRoute('medical-records.history', $patient)
            ->assertSessionHas('status');

        $consultation = MedicalConsultation::query()->firstOrFail();
        $this->assertSame($appointment->id, $consultation->appointment_id);
        $this->assertSame($patient->id, $consultation->patient_id);
        $this->assertSame($doctor->id, $consultation->doctor_user_id);
        $this->assertSame('completed', $appointment->fresh()->status);
        $this->assertDatabaseCount('medical_consultations', 1);

        $this->actingAs($doctor)
            ->get(route('medical-records.history', $patient))
            ->assertOk()
            ->assertSeeText('Diagnóstico profesional')
            ->assertSeeText('Tratamiento indicado')
            ->assertSeeText('Control posterior')
            ->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("x")</script>', false);

        $this->actingAs($patient)
            ->get(route('appointments.mine'))
            ->assertOk()
            ->assertSeeText('Atendida')
            ->assertDontSee(route('appointments.cancel', $appointment), false);
    }

    public function test_patient_history_includes_previous_consultation_and_nursing_measurements(): void
    {
        $this->travelTo('2026-10-20 10:00:00');
        $doctor = $this->makeStaffUser('doctor', 'MED-102');
        $nurse = $this->makeStaffUser('nurse', 'ENF-102');
        $patient = $this->makeUser('patient', ['name' => 'Paciente Con Historial']);
        $appointment = $this->makeAppointment($doctor, $patient, '2026-10-19', 'completed');
        $appointment->medicalConsultation()->create([
            'patient_id' => $patient->id,
            'doctor_user_id' => $doctor->id,
            'reason' => 'Consulta anterior',
            'observations' => 'Observaciones anteriores',
            'diagnosis' => 'Diagnóstico anterior',
            'treatment' => 'Tratamiento previo',
            'recommendations' => 'Recomendaciones previas',
        ]);
        $appointment->nursingIntake()->create([
            'patient_id' => $patient->id,
            'nurse_user_id' => $nurse->id,
            'reason' => 'Motivo de recepción',
            'blood_pressure_systolic' => 104,
            'blood_pressure_diastolic' => 68,
            'measured_at' => now(),
        ]);

        $this->actingAs($doctor)
            ->get(route('medical-records.history', $patient))
            ->assertOk()
            ->assertSeeText('Diagnóstico anterior')
            ->assertSeeText('Tratamiento previo')
            ->assertSeeText('104 / 68 mmHg')
            ->assertSeeText('Motivo de recepción')
            ->assertSeeText($nurse->name);
    }

    public function test_doctor_without_an_appointment_for_patient_cannot_open_history_or_consultation(): void
    {
        $this->travelTo('2026-10-20 10:00:00');
        $assignedDoctor = $this->makeStaffUser('doctor', 'MED-103');
        $otherDoctor = $this->makeStaffUser('doctor', 'MED-104');
        $patient = $this->makeUser('patient');
        $appointment = $this->makeAppointment($assignedDoctor, $patient, '2026-10-20', 'confirmed');

        $this->actingAs($otherDoctor)
            ->get(route('medical-records.history', $patient))
            ->assertNotFound();

        $this->actingAs($otherDoctor)
            ->get(route('medical-consultations.create', $appointment))
            ->assertNotFound();

        $this->actingAs($otherDoctor)
            ->post(route('medical-consultations.store', $appointment), $this->validConsultation())
            ->assertNotFound();

        $this->assertDatabaseCount('medical_consultations', 0);
        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_non_doctor_roles_cannot_open_patient_history_or_consultation_form(): void
    {
        $doctor = $this->makeStaffUser('doctor', 'MED-105');
        $patient = $this->makeUser('patient');
        $appointment = $this->makeAppointment($doctor, $patient, '2026-10-20', 'confirmed');

        foreach (['patient', 'nurse', 'staff', 'admin'] as $role) {
            $user = $role === 'nurse'
                ? $this->makeStaffUser('nurse', 'ENF-'.$role)
                : $this->makeUser($role);

            $this->actingAs($user)
                ->get(route('medical-records.history', $patient))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('medical-consultations.create', $appointment))
                ->assertForbidden();
        }
    }

    public function test_consultation_requires_all_clinical_fields_and_does_not_change_appointment_on_validation_error(): void
    {
        $this->travelTo('2026-10-20 10:00:00');
        $doctor = $this->makeStaffUser('doctor', 'MED-106');
        $patient = $this->makeUser('patient');
        $appointment = $this->makeAppointment($doctor, $patient, '2026-10-20', 'confirmed');
        $data = $this->validConsultation();
        unset($data['recommendations']);

        $this->actingAs($doctor)
            ->from(route('medical-consultations.create', $appointment))
            ->post(route('medical-consultations.store', $appointment), $data)
            ->assertRedirect(route('medical-consultations.create', $appointment))
            ->assertSessionHasErrors('recommendations');

        $this->assertDatabaseCount('medical_consultations', 0);
        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_only_confirmed_appointments_that_have_started_can_be_consulted(): void
    {
        $this->travelTo('2026-10-20 10:00:00');
        $doctor = $this->makeStaffUser('doctor', 'MED-107');
        $patient = $this->makeUser('patient');
        $pendingAppointment = $this->makeAppointment($doctor, $patient, '2026-10-20', 'pending');
        $futureAppointment = $this->makeAppointment($doctor, $patient, '2026-10-20', 'confirmed', '11:00');

        $this->actingAs($doctor)
            ->get(route('medical-consultations.create', $pendingAppointment))
            ->assertNotFound();

        $this->actingAs($doctor)
            ->get(route('medical-consultations.create', $futureAppointment))
            ->assertNotFound();

        $this->actingAs($doctor)
            ->post(route('medical-consultations.store', $pendingAppointment), $this->validConsultation())
            ->assertSessionHasErrors('appointment');

        $this->assertDatabaseCount('medical_consultations', 0);
        $this->assertSame('pending', $pendingAppointment->fresh()->status);
        $this->assertSame('confirmed', $futureAppointment->fresh()->status);
    }

    public function test_a_completed_appointment_cannot_receive_a_second_consultation(): void
    {
        $this->travelTo('2026-10-20 10:00:00');
        $doctor = $this->makeStaffUser('doctor', 'MED-108');
        $patient = $this->makeUser('patient');
        $appointment = $this->makeAppointment($doctor, $patient, '2026-10-20', 'completed');
        $appointment->medicalConsultation()->create([
            'patient_id' => $patient->id,
            'doctor_user_id' => $doctor->id,
            ...$this->validConsultation(),
        ]);

        $this->actingAs($doctor)
            ->post(route('medical-consultations.store', $appointment), $this->validConsultation())
            ->assertSessionHasErrors('appointment');

        $this->assertDatabaseCount('medical_consultations', 1);
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'completed']);
    }

    /**
     * @return array<string, string>
     */
    private function validConsultation(): array
    {
        return [
            'reason' => 'Motivo de consulta',
            'observations' => 'Observaciones médicas',
            'diagnosis' => 'Diagnóstico registrado',
            'treatment' => 'Tratamiento indicado',
            'recommendations' => 'Recomendaciones médicas',
        ];
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

    private function makeAppointment(
        User $doctor,
        User $patient,
        string $date,
        string $status,
        string $startTime = '09:00',
    ): Appointment {
        return Appointment::query()->create([
            'staff_profile_id' => $doctor->staffProfile->id,
            'patient_id' => $patient->id,
            'appointment_date' => $date,
            'start_time' => $startTime,
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
