<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintThreeInstitutionalReportsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_administrator_can_view_monthly_patient_appointment_and_doctor_activity_reports(): void
    {
        $admin = $this->makeUser('admin');
        $patient = $this->makeUser('patient', [
            'created_at' => '2026-10-12 09:00:00',
            'updated_at' => '2026-10-12 09:00:00',
        ]);
        $doctor = $this->makeDoctor('Dra. Ana Pérez');
        $this->makeAppointment($doctor, $patient, '2026-10-02', 'completed');
        $this->makeAppointment($doctor, $patient, '2026-10-03', 'confirmed');
        $this->makeAppointment($doctor, $patient, '2026-09-30', 'cancelled');

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['month' => '2026-10']))
            ->assertOk()
            ->assertSeeText('Pacientes registrados durante el mes')
            ->assertSeeText('Dra. Ana Pérez')
            ->assertSeeText('Consultas completadas')
            ->assertSeeText('50.0%')
            ->assertSeeText('no evalúa resultados clínicos ni calidad profesional');
    }

    public function test_administrator_can_download_csv_with_monthly_aggregates_and_safe_doctor_names(): void
    {
        $admin = $this->makeUser('admin');
        $doctor = $this->makeDoctor('=Medico fórmula');

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export', ['month' => '2026-10']));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename=reporte-institucional-2026-10.csv')
            ->assertStreamed()
            ->assertStreamedContent(
                "\"Reporte institucional del Centro de Salud San Juan\",2026-10\n".
                "\"Pacientes registrados en el mes\",0\n".
                "\"Citas registradas en el mes\",0\n".
                "\"Citas pendientes\",0\n".
                "\"Citas confirmadas\",0\n".
                "\"Citas atendidas\",0\n".
                "\"Citas canceladas\",0\n".
                "\n".
                "Médico,\"Citas asignadas\",\"Consultas completadas\",\"Atención (%)\"\n".
                "\"'=Medico fórmula\",0,0,0\n",
            );

        $this->assertModelExists($doctor);
    }

    public function test_invalid_month_is_rejected_and_non_administrators_cannot_access_reports(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->from(route('admin.reports.index'))
            ->get(route('admin.reports.index', ['month' => '2026-99']))
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHasErrors('month');

        foreach (['doctor', 'nurse', 'staff', 'patient'] as $role) {
            $user = $role === 'doctor'
                ? $this->makeDoctor()
                : $this->makeUser($role);

            $this->actingAs($user)->get(route('admin.reports.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.reports.export'))->assertForbidden();
        }
    }

    private function makeUser(string $role, array $attributes = []): User
    {
        return User::factory()->create([
            'role_id' => $this->role($role)->id,
            ...$attributes,
        ]);
    }

    private function makeDoctor(string $name = 'Médico de prueba'): User
    {
        $doctor = $this->makeUser('doctor', ['name' => $name]);
        $staffProfile = $doctor->staffProfile()->create([
            'employee_number' => 'MED-'.$doctor->id,
            'position' => 'Médico',
        ]);
        $specialty = Specialty::query()->create([
            'name' => 'Especialidad '.$doctor->id,
            'is_active' => true,
        ]);
        $staffProfile->specialties()->attach($specialty);

        return $doctor->load('staffProfile');
    }

    private function makeAppointment(User $doctor, User $patient, string $date, string $status): Appointment
    {
        return Appointment::query()->create([
            'staff_profile_id' => $doctor->staffProfile->id,
            'patient_id' => $patient->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'status' => $status,
        ]);
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
}
