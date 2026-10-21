<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\CareEncounter;
use App\Models\MedicalConsultation;
use App\Models\NursingIntake;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintFourFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_administrative_staff_can_check_in_an_appointment_without_marking_it_attended(): void
    {
        $this->travelTo('2026-10-21 08:00:00');
        $admissionStaff = $this->makeStaffUser('staff');
        $nurse = $this->makeStaffUser('nurse');
        $doctor = $this->makeStaffUser('doctor');
        $patient = $this->makePatientAccount();
        $appointment = $this->makeAppointment($doctor, $patient, '2026-10-21', '09:00');

        $this->actingAs($admissionStaff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Panel de admisión')
            ->assertSee(route('reception.index'), false);

        $this->actingAs($admissionStaff)
            ->get(route('reception.index'))
            ->assertOk()
            ->assertSeeText($patient->name)
            ->assertSeeText($appointment->booking_code);

        $this->actingAs($admissionStaff)
            ->post(route('reception.appointments.check-in', $appointment))
            ->assertRedirectToRoute('reception.index')
            ->assertSessionHas('status');

        $encounter = CareEncounter::query()->firstOrFail();
        $this->assertSame($patient->patientProfile->id, $encounter->patient_profile_id);
        $this->assertSame($appointment->id, $encounter->appointment_id);
        $this->assertSame($admissionStaff->id, $encounter->received_by_user_id);
        $this->assertSame('waiting', $encounter->status);
        $this->assertSame('confirmed', $appointment->fresh()->status);
        $this->assertDatabaseHas('care_encounter_events', [
            'care_encounter_id' => $encounter->id,
            'author_user_id' => $admissionStaff->id,
            'to_status' => 'waiting',
        ]);

        $this->actingAs($nurse)
            ->get(route('nursing.intakes.index'))
            ->assertOk()
            ->assertSeeText($patient->name);
    }

    public function test_legacy_reception_role_retains_access_to_admission(): void
    {
        $legacyReceptionist = $this->makeStaffUser('receptionist');

        $this->actingAs($legacyReceptionist)
            ->get(route('reception.index'))
            ->assertOk()
            ->assertSeeText('Atención y recepción');
    }

    public function test_walk_in_is_created_without_a_portal_account_and_completes_the_nursing_and_doctor_flow(): void
    {
        $this->travelTo('2026-10-21 08:00:00');
        $admissionStaff = $this->makeStaffUser('staff');
        $nurse = $this->makeStaffUser('nurse');
        $doctor = $this->makeStaffUser('doctor');

        $this->actingAs($admissionStaff)
            ->post(route('reception.walk-ins.store'), [
                'name' => 'Paciente Presencial',
                'national_id' => 'WALK-100',
                'birth_date' => '1990-05-12',
                'sex' => 'F',
                'doctor_user_id' => $doctor->id,
            ])
            ->assertRedirectToRoute('reception.index')
            ->assertSessionHas('status');

        $profile = PatientProfile::query()->where('national_id', 'WALK-100')->firstOrFail();
        $this->assertNull($profile->user_id);
        $this->assertSame('Paciente Presencial', $profile->name);
        $this->assertDatabaseMissing('users', ['email' => 'WALK-100']);

        $encounter = CareEncounter::query()->where('patient_profile_id', $profile->id)->firstOrFail();
        $this->assertNull($encounter->appointment_id);
        $this->assertSame('walk_in', $encounter->source);

        $this->actingAs($nurse)
            ->post(route('nursing.encounters.store', $encounter), [
                'reason' => 'Control presencial',
                'heart_rate' => 75,
            ])
            ->assertRedirectToRoute('nursing.intakes.index');

        $intake = NursingIntake::query()->firstOrFail();
        $this->assertSame($encounter->id, $intake->care_encounter_id);
        $this->assertSame($profile->id, $intake->patient_profile_id);
        $this->assertNull($intake->appointment_id);
        $this->assertNull($intake->patient_id);
        $this->assertSame('pending_consultation', $encounter->fresh()->status);

        $this->actingAs($doctor)
            ->get(route('nursing.encounters.doctor-show', $encounter))
            ->assertOk()
            ->assertSeeText('Paciente Presencial')
            ->assertSeeText('Control presencial');

        $this->actingAs($doctor)
            ->get(route('medical-consultations.encounters.create', $encounter))
            ->assertOk()
            ->assertSeeText('Paciente Presencial');

        $this->actingAs($doctor)
            ->post(route('medical-consultations.encounters.store', $encounter), [
                'reason' => 'Control presencial',
                'observations' => 'Evaluación registrada.',
                'diagnosis' => 'Diagnóstico documentado por profesional.',
                'treatment' => 'Tratamiento registrado.',
                'recommendations' => 'Seguimiento según indicación.',
            ])
            ->assertRedirectToRoute('medical-records.encounters.history', $encounter)
            ->assertSessionHas('status');

        $consultation = MedicalConsultation::query()->firstOrFail();
        $this->assertSame($encounter->id, $consultation->care_encounter_id);
        $this->assertSame($profile->id, $consultation->patient_profile_id);
        $this->assertNull($consultation->appointment_id);
        $this->assertSame('attended', $encounter->fresh()->status);

        $this->actingAs($doctor)
            ->get(route('medical-records.encounters.history', $encounter))
            ->assertOk()
            ->assertSeeText('Diagnóstico documentado por profesional.');
    }

    public function test_reception_rejects_duplicate_walk_in_identity_and_other_roles_cannot_admit_patients(): void
    {
        $admissionStaff = $this->makeStaffUser('staff');
        $nurse = $this->makeStaffUser('nurse');
        $patient = $this->makePatientAccount();
        $doctor = $this->makeStaffUser('doctor');

        $this->actingAs($admissionStaff)
            ->from(route('reception.index'))
            ->post(route('reception.walk-ins.store'), [
                'name' => 'Paciente Duplicado',
                'national_id' => $patient->patientProfile->national_id,
                'birth_date' => '1990-05-12',
                'sex' => 'F',
                'doctor_user_id' => $doctor->id,
            ])
            ->assertRedirect(route('reception.index'))
            ->assertSessionHasErrors('national_id');

        $this->assertDatabaseCount('patient_profiles', 1);
        $this->actingAs($nurse)->get(route('reception.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('reception.index'))->assertForbidden();
    }

    public function test_reception_can_mark_a_past_appointment_as_no_show_and_rejects_future_appointments(): void
    {
        $this->travelTo('2026-10-21 10:00:00');
        $admissionStaff = $this->makeStaffUser('staff');
        $doctor = $this->makeStaffUser('doctor');
        $pastAppointment = $this->makeAppointment(
            $doctor,
            $this->makePatientAccount(),
            '2026-10-21',
            '09:00',
        );
        $futureAppointment = $this->makeAppointment(
            $doctor,
            $this->makePatientAccount(),
            '2026-10-21',
            '11:00',
        );

        $this->actingAs($admissionStaff)
            ->get(route('reception.index'))
            ->assertOk()
            ->assertSee(route('reception.appointments.no-show', $pastAppointment), false)
            ->assertDontSee(route('reception.appointments.no-show', $futureAppointment), false);

        $this->actingAs($admissionStaff)
            ->post(route('reception.appointments.no-show', $pastAppointment))
            ->assertRedirectToRoute('reception.index')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('appointments', [
            'id' => $pastAppointment->id,
            'status' => 'no_show',
        ]);
        $this->assertDatabaseHas('care_encounter_events', [
            'appointment_id' => $pastAppointment->id,
            'author_user_id' => $admissionStaff->id,
            'from_status' => 'confirmed',
            'to_status' => 'no_show',
        ]);

        $this->actingAs($admissionStaff)
            ->post(route('reception.appointments.no-show', $futureAppointment))
            ->assertSessionHasErrors('appointment');

        $this->assertDatabaseHas('appointments', [
            'id' => $futureAppointment->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_reception_can_start_a_walk_in_for_an_existing_patient_profile_without_creating_a_duplicate(): void
    {
        $admissionStaff = $this->makeStaffUser('staff');
        $doctor = $this->makeStaffUser('doctor');
        $patient = $this->makePatientAccount();

        $this->actingAs($admissionStaff)
            ->get(route('reception.index', ['q' => $patient->patientProfile->national_id]))
            ->assertOk()
            ->assertSeeText($patient->name)
            ->assertSeeText('Iniciar atención');

        $this->actingAs($admissionStaff)
            ->post(route('reception.walk-ins.store'), [
                'patient_profile_id' => $patient->patientProfile->id,
                'doctor_user_id' => $doctor->id,
            ])
            ->assertRedirectToRoute('reception.index')
            ->assertSessionHas('status');

        $this->assertDatabaseCount('patient_profiles', 1);
        $this->assertDatabaseHas('care_encounters', [
            'patient_profile_id' => $patient->patientProfile->id,
            'appointment_id' => null,
            'doctor_user_id' => $doctor->id,
            'source' => 'walk_in',
        ]);
    }

    public function test_only_the_assigned_doctor_can_access_a_walk_in_clinical_history(): void
    {
        $admissionStaff = $this->makeStaffUser('staff');
        $doctor = $this->makeStaffUser('doctor');
        $otherDoctor = $this->makeStaffUser('doctor');

        $this->actingAs($admissionStaff)->post(route('reception.walk-ins.store'), [
            'name' => 'Historia Protegida',
            'national_id' => 'WALK-200',
            'birth_date' => '1990-05-12',
            'sex' => 'F',
            'doctor_user_id' => $doctor->id,
        ])->assertRedirectToRoute('reception.index');

        $encounter = CareEncounter::query()->firstOrFail();

        $this->actingAs($otherDoctor)
            ->get(route('medical-records.encounters.history', $encounter))
            ->assertNotFound();
        $this->actingAs($otherDoctor)
            ->get(route('medical-consultations.encounters.create', $encounter))
            ->assertNotFound();
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role_id' => $this->role($role)->id,
        ]);
    }

    private function makePatientAccount(): User
    {
        $patient = $this->makeUser('patient');
        $patient->patientProfile()->create([
            'name' => $patient->name,
            'national_id' => 'PAT-'.$patient->id,
            'birth_date' => '1995-06-12',
            'sex' => 'F',
        ]);

        return $patient->load('patientProfile');
    }

    private function makeStaffUser(string $role): User
    {
        $user = $this->makeUser($role);
        $user->staffProfile()->create([
            'employee_number' => strtoupper($role).'-'.$user->id,
            'position' => $role,
        ]);

        return $user->load('staffProfile');
    }

    private function makeAppointment(User $doctor, User $patient, string $date, string $time): Appointment
    {
        return Appointment::query()->create([
            'staff_profile_id' => $doctor->staffProfile->id,
            'patient_id' => $patient->id,
            'appointment_date' => $date,
            'start_time' => $time,
            'status' => 'confirmed',
        ]);
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
