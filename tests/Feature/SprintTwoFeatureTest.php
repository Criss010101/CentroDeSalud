<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintTwoFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_sprint_two_staff_roles_are_added_by_database_migrations(): void
    {
        $this->assertSame('nurse', Role::query()->where('name', 'Enfermería')->value('code'));
        $this->assertSame('staff', Role::query()->where('name', 'Personal administrativo')->value('code'));
    }

    public function test_administrator_can_manage_staff_accounts_and_assign_doctors_a_specialty(): void
    {
        $admin = $this->makeUser('admin');
        $specialty = Specialty::query()->create(['name' => 'Medicina General', 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Dra. Ana Pérez',
            'email' => 'ana.doctora@example.test',
            'role_id' => $this->role('doctor')->id,
            'employee_number' => 'MED-001',
            'position' => 'Médica general',
            'hire_date' => '2024-03-01',
            'phone' => '70000001',
            'password' => 'ClaveSegura2026!',
            'password_confirmation' => 'ClaveSegura2026!',
            'specialty_ids' => [$specialty->id],
        ]);

        $response->assertRedirectToRoute('admin.staff.index');
        $doctor = User::query()->where('email', 'ana.doctora@example.test')->firstOrFail();
        $this->assertSame('doctor', $doctor->role->code);
        $this->assertModelExists($doctor->staffProfile);
        $this->assertSame('MED-001', $doctor->staffProfile->employee_number);
        $this->assertTrue($doctor->staffProfile->specialties->contains($specialty));
    }

    public function test_administrator_can_open_sprint_two_management_pages(): void
    {
        $admin = $this->makeUser('admin');
        $specialty = Specialty::query()->create(['name' => 'Medicina General', 'is_active' => true]);
        $doctor = $this->makeDoctor($specialty);

        $this->actingAs($admin)->get(route('admin.staff.index'))->assertOk()->assertSeeText($doctor->name);
        $this->actingAs($admin)->get(route('admin.staff.create'))->assertOk()->assertSeeText('Registrar personal');
        $this->actingAs($admin)->get(route('admin.staff.edit', $doctor->staffProfile))->assertOk()->assertSeeText('Editar personal');
        $this->actingAs($admin)->get(route('admin.specialties.index'))->assertOk()->assertSeeText('Medicina General');
        $this->actingAs($admin)->get(route('admin.specialties.create'))->assertOk()->assertSeeText('Nueva especialidad');
        $this->actingAs($admin)->get(route('admin.specialties.edit', $specialty))->assertOk()->assertSeeText('Editar especialidad');
        $this->actingAs($admin)->get(route('admin.staff.schedules.edit', $doctor->staffProfile))->assertOk()->assertSeeText('Disponibilidad semanal');
    }

    public function test_doctor_cannot_be_registered_without_a_specialty(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->from(route('admin.staff.create'))
            ->post(route('admin.staff.store'), [
                'name' => 'Doctor Sin Especialidad',
                'email' => 'doctor@example.test',
                'role_id' => $this->role('doctor')->id,
                'employee_number' => 'MED-002',
                'position' => 'Médico',
                'password' => 'ClaveSegura2026!',
                'password_confirmation' => 'ClaveSegura2026!',
            ])
            ->assertRedirect(route('admin.staff.create'))
            ->assertSessionHasErrors('specialty_ids');

        $this->assertDatabaseMissing('users', ['email' => 'doctor@example.test']);
    }

    public function test_only_administrators_can_manage_staff_specialties_and_schedules(): void
    {
        $patient = $this->makePatient('paciente@example.test', '12345678');
        $doctor = $this->makeDoctor();

        $this->actingAs($patient)->get(route('admin.staff.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.specialties.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.staff.schedules.edit', $doctor->staffProfile))->assertForbidden();
        $this->actingAs($doctor)->get(route('admin.staff.index'))->assertForbidden();
    }

    public function test_staff_accounts_cannot_be_changed_or_deleted_through_general_user_management(): void
    {
        $admin = $this->makeUser('admin');
        $nurse = $this->makeUser('nurse');
        $nurse->staffProfile()->create([
            'employee_number' => 'ENF-001',
            'position' => 'Enfermera',
        ]);

        $this->actingAs($admin)->get(route('admin.users.edit', $nurse))->assertNotFound();
        $this->actingAs($admin)->put(route('admin.users.update', $nurse), [])->assertNotFound();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $nurse))->assertNotFound();

        $this->assertModelExists($nurse);
    }

    public function test_administrator_can_create_specialties_and_cannot_delete_assigned_specialties(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post(route('admin.specialties.store'), [
            'name' => 'Pediatría',
            'description' => 'Atención pediátrica',
            'is_active' => '1',
        ])->assertRedirectToRoute('admin.specialties.index');

        $specialty = Specialty::query()->where('name', 'Pediatría')->firstOrFail();
        $doctor = $this->makeDoctor($specialty);

        $this->actingAs($admin)
            ->delete(route('admin.specialties.destroy', $specialty))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('specialties', ['id' => $specialty->id]);
        $this->assertModelExists($doctor->staffProfile);
    }

    public function test_administrator_can_save_weekly_doctor_availability_and_invalid_hours_are_rejected(): void
    {
        $admin = $this->makeUser('admin');
        $doctor = $this->makeDoctor();
        $weekday = today()->addDay()->dayOfWeekIso;

        $this->actingAs($admin)
            ->put(route('admin.staff.schedules.update', $doctor->staffProfile), [
                'schedules' => [
                    $weekday => ['enabled' => '1', 'start_time' => '08:00', 'end_time' => '09:30'],
                ],
            ])
            ->assertRedirectToRoute('admin.staff.index');

        $this->assertDatabaseHas('doctor_schedules', [
            'staff_profile_id' => $doctor->staffProfile->id,
            'day_of_week' => $weekday,
            'start_time' => '08:00',
            'end_time' => '09:30',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.staff.schedules.edit', $doctor->staffProfile))
            ->put(route('admin.staff.schedules.update', $doctor->staffProfile), [
                'schedules' => [
                    $weekday => ['enabled' => '1', 'start_time' => '10:00', 'end_time' => '09:00'],
                ],
            ])
            ->assertRedirect(route('admin.staff.schedules.edit', $doctor->staffProfile))
            ->assertSessionHasErrors("schedules.$weekday.end_time");

        $this->assertDatabaseHas('doctor_schedules', [
            'staff_profile_id' => $doctor->staffProfile->id,
            'day_of_week' => $weekday,
            'start_time' => '08:00',
            'end_time' => '09:30',
        ]);
    }

    public function test_patient_can_view_book_confirm_cancel_and_rebook_an_available_slot(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $patient = $this->makePatient('ana@example.test', '11111111');
        $otherPatient = $this->makePatient('bruno@example.test', '22222222');
        $doctor = $this->makeDoctor();
        $appointmentDate = today()->addDay();
        $schedule = $doctor->staffProfile->schedules()->create([
            'day_of_week' => $appointmentDate->dayOfWeekIso,
            'start_time' => '08:00',
            'end_time' => '09:30',
        ]);

        $this->actingAs($patient)
            ->get(route('appointments.availability', ['date' => $appointmentDate->toDateString()]))
            ->assertOk()
            ->assertSeeText($doctor->name)
            ->assertSee('value="08:00"', false);

        $this->actingAs($patient)->post(route('appointments.store'), [
            'staff_profile_id' => $doctor->staffProfile->id,
            'appointment_date' => $appointmentDate->toDateString(),
            'start_time' => '08:00',
            'reason' => 'Consulta de rutina',
        ])->assertRedirectToRoute('appointments.mine');

        $appointment = Appointment::query()->where('patient_id', $patient->id)->firstOrFail();
        $this->assertSame('pending', $appointment->status);
        $this->assertSame($schedule->staff_profile_id, $appointment->staff_profile_id);

        $this->actingAs($otherPatient)
            ->from(route('appointments.availability', ['date' => $appointmentDate->toDateString()]))
            ->post(route('appointments.store'), [
                'staff_profile_id' => $doctor->staffProfile->id,
                'appointment_date' => $appointmentDate->toDateString(),
                'start_time' => '08:00',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('appointments', 1);

        $this->actingAs($patient)
            ->post(route('appointments.confirm', $appointment))
            ->assertRedirect();
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'confirmed']);

        $this->actingAs($patient)
            ->post(route('appointments.cancel', $appointment))
            ->assertRedirect();
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'cancelled']);

        $this->actingAs($otherPatient)
            ->post(route('appointments.store'), [
                'staff_profile_id' => $doctor->staffProfile->id,
                'appointment_date' => $appointmentDate->toDateString(),
                'start_time' => '08:00',
            ])
            ->assertRedirectToRoute('appointments.mine');

        $this->assertDatabaseCount('appointments', 2);
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $otherPatient->id,
            'status' => 'pending',
            'start_time' => '08:00',
        ]);
    }

    public function test_patient_cannot_read_or_cancel_another_patients_appointment(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $owner = $this->makePatient('owner@example.test', '33333333');
        $otherPatient = $this->makePatient('other@example.test', '44444444');
        $doctor = $this->makeDoctor();
        $appointmentDate = today()->addDay();
        $appointment = $this->makeAppointment($doctor, $owner, $appointmentDate->toDateString());

        $this->actingAs($otherPatient)
            ->post(route('appointments.cancel', $appointment))
            ->assertNotFound();

        $this->actingAs($otherPatient)
            ->get(route('appointments.mine'))
            ->assertOk()
            ->assertDontSeeText($owner->name);
    }

    public function test_administrator_can_schedule_a_confirmed_appointment_and_doctor_can_view_daily_agenda(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $admin = $this->makeUser('admin');
        $patient = $this->makePatient('patient@example.test', '55555555');
        $doctor = $this->makeDoctor();
        $appointmentDate = today()->addDay();
        $doctor->staffProfile->schedules()->create([
            'day_of_week' => $appointmentDate->dayOfWeekIso,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->actingAs($admin)->get(route('admin.appointments.create', [
            'date' => $appointmentDate->toDateString(),
            'staff_profile_id' => $doctor->staffProfile->id,
        ]))->assertOk()->assertSeeText($patient->name)->assertSee('10:00', false);

        $this->actingAs($admin)->post(route('admin.appointments.store'), [
            'patient_id' => $patient->id,
            'staff_profile_id' => $doctor->staffProfile->id,
            'appointment_date' => $appointmentDate->toDateString(),
            'start_time' => '10:00',
            'reason' => 'Control presencial',
        ])->assertRedirectToRoute('admin.appointments.create');

        $appointment = Appointment::query()->where('patient_id', $patient->id)->firstOrFail();
        $this->assertSame('confirmed', $appointment->status);

        $this->actingAs($doctor)
            ->get(route('appointments.doctor-agenda', ['date' => $appointmentDate->toDateString()]))
            ->assertOk()
            ->assertSeeText($patient->name)
            ->assertSeeText('Control presencial');
    }

    private function role(string $code): Role
    {
        $names = [
            'admin' => 'Administrador',
            'doctor' => 'Médico',
            'nurse' => 'Enfermería',
            'staff' => 'Personal administrativo',
            'patient' => 'Paciente',
        ];

        return Role::query()->firstOrCreate(['code' => $code], ['name' => $names[$code]]);
    }

    private function makeUser(string $role, array $attributes = []): User
    {
        return User::factory()->create([
            'role_id' => $this->role($role)->id,
            ...$attributes,
        ]);
    }

    private function makePatient(string $email, string $nationalId): User
    {
        $patient = $this->makeUser('patient', ['email' => $email]);
        $patient->patientProfile()->create([
            'national_id' => $nationalId,
            'birth_date' => '1995-06-12',
            'sex' => 'F',
        ]);

        return $patient;
    }

    private function makeDoctor(?Specialty $specialty = null): User
    {
        $doctor = $this->makeUser('doctor');
        $staffProfile = $doctor->staffProfile()->create([
            'employee_number' => 'MED-'.$doctor->id,
            'position' => 'Médico especialista',
        ]);
        $specialty ??= Specialty::query()->create(['name' => 'Medicina General', 'is_active' => true]);
        $staffProfile->specialties()->attach($specialty);

        return $doctor->load('staffProfile');
    }

    private function makeAppointment(User $doctor, User $patient, string $date): Appointment
    {
        return Appointment::query()->create([
            'staff_profile_id' => $doctor->staffProfile->id,
            'patient_id' => $patient->id,
            'appointment_date' => $date,
            'start_time' => '08:00',
            'status' => 'pending',
        ]);
    }
}
