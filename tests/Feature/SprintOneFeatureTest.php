<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintOneFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_can_open_the_login_and_patient_registration_pages(): void
    {
        $this->get(route('login'))->assertOk()->assertSeeText('Iniciar sesión');
        $this->get(route('register'))->assertOk()->assertSeeText('Registro de paciente');
    }

    public function test_patient_can_register_and_create_a_personal_record(): void
    {
        $this->role('patient');

        $response = $this->post(route('register.store'), [
            'name' => 'Ana Perez',
            'email' => 'ana@example.test',
            'password' => 'SaludSegura2026!',
            'password_confirmation' => 'SaludSegura2026!',
            'national_id' => '12345678',
            'birth_date' => '1995-06-12',
            'sex' => 'F',
            'phone' => '70000000',
            'address' => 'Calle Central 10',
        ]);

        $response->assertRedirectToRoute('dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ana@example.test']);
        $this->assertDatabaseHas('patient_profiles', [
            'national_id' => '12345678',
            'sex' => 'F',
            'phone' => '70000000',
        ]);
        $this->assertNotSame('SaludSegura2026!', User::query()->where('email', 'ana@example.test')->value('password'));
    }

    public function test_patient_registration_rejects_duplicate_identity_numbers(): void
    {
        $this->makePatient('existing@example.test', '12345678');

        $response = $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Another Patient',
                'email' => 'another@example.test',
                'password' => 'SaludSegura2026!',
                'password_confirmation' => 'SaludSegura2026!',
                'national_id' => '12345678',
                'birth_date' => '1995-06-12',
                'sex' => 'F',
            ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'national_id' => 'El valor de carnet de identidad ya está registrado.',
        ]);
        $this->assertDatabaseMissing('users', ['email' => 'another@example.test']);
    }

    public function test_user_can_sign_in_and_the_session_is_regenerated(): void
    {
        $patient = $this->makePatient('ana@example.test', '12345678');
        $oldSessionId = session()->getId();

        $response = $this->post(route('login.store'), [
            'email' => $patient->email,
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute('dashboard');
        $this->assertAuthenticatedAs($patient);
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public function test_authenticated_user_can_sign_out_and_the_session_is_invalidated(): void
    {
        $patient = $this->makePatient('ana@example.test', '12345678');

        $this->actingAs($patient)
            ->post(route('logout'))
            ->assertRedirectToRoute('login');

        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected_without_authenticating(): void
    {
        $this->makePatient('ana@example.test', '12345678');

        $response = $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'ana@example.test',
                'password' => 'incorrect-password',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_patient_can_update_only_their_own_personal_record(): void
    {
        $patient = $this->makePatient('ana@example.test', '12345678');

        $this->actingAs($patient)
            ->get(route('patients.profile.edit'))
            ->assertOk()
            ->assertSeeText('Mi ficha de paciente');

        $response = $this->actingAs($patient)->put(route('patients.profile.update'), [
            'name' => 'Ana Perez Updated',
            'email' => 'ana.updated@example.test',
            'national_id' => '12345678',
            'birth_date' => '1995-06-12',
            'sex' => 'F',
            'phone' => '71111111',
            'address' => 'Nueva dirección',
        ]);

        $response->assertRedirectToRoute('patients.profile.edit');
        $this->assertDatabaseHas('users', ['id' => $patient->id, 'name' => 'Ana Perez Updated', 'email' => 'ana.updated@example.test']);
        $this->assertDatabaseHas('patient_profiles', ['user_id' => $patient->id, 'phone' => '71111111', 'address' => 'Nueva dirección']);
    }

    public function test_patient_cannot_access_staff_patient_directory_or_user_management(): void
    {
        $patient = $this->makePatient('ana@example.test', '12345678');

        $this->actingAs($patient)->get(route('patients.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_doctor_can_search_patients_but_cannot_manage_accounts(): void
    {
        $doctor = $this->makeUser('doctor', ['email' => 'doctor@example.test']);
        $this->makePatient('ana@example.test', '12345678', ['name' => 'Ana Perez', 'sex' => 'F']);
        $this->makePatient('bruno@example.test', '87654321', ['name' => 'Bruno Rojas', 'sex' => 'M']);

        $response = $this->actingAs($doctor)->get(route('patients.index', ['q' => 'Ana', 'sex' => 'F']));

        $response->assertOk();
        $response->assertSeeText('Ana Perez');
        $response->assertDontSeeText('Bruno Rojas');
        $this->actingAs($doctor)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_administrator_can_create_a_patient_account_and_assign_its_role(): void
    {
        $admin = $this->makeUser('admin', ['email' => 'admin@example.test']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Carla Flores',
            'email' => 'carla@example.test',
            'role_id' => $this->role('patient')->id,
            'password' => 'ClaveSegura2026!',
            'password_confirmation' => 'ClaveSegura2026!',
            'national_id' => '99887766',
            'birth_date' => '1988-01-24',
            'sex' => 'F',
        ]);

        $response->assertRedirectToRoute('admin.users.index');
        $createdUser = User::query()->where('email', 'carla@example.test')->firstOrFail();
        $this->assertSame('patient', $createdUser->role->code);
        $this->assertDatabaseHas('patient_profiles', ['user_id' => $createdUser->id, 'national_id' => '99887766']);
    }

    public function test_administrator_can_update_and_delete_an_account(): void
    {
        $admin = $this->makeUser('admin', ['email' => 'admin@example.test']);
        $doctor = $this->makeUser('doctor', ['email' => 'doctor@example.test']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $doctor), [
            'name' => 'Doctor Updated',
            'email' => 'doctor.updated@example.test',
            'role_id' => $this->role('doctor')->id,
        ]);

        $response->assertRedirectToRoute('admin.users.index');
        $this->assertDatabaseHas('users', [
            'id' => $doctor->id,
            'name' => 'Doctor Updated',
            'email' => 'doctor.updated@example.test',
        ]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $doctor))
            ->assertRedirectToRoute('admin.users.index');
        $this->assertDatabaseMissing('users', ['id' => $doctor->id]);
    }

    public function test_administrator_can_view_account_management_pages(): void
    {
        $admin = $this->makeUser('admin', ['email' => 'admin@example.test']);
        $patient = $this->makePatient('ana@example.test', '12345678');

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSeeText('Usuarios y roles')
            ->assertSeeText('ana@example.test');

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSeeText('Crear usuario');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $patient))
            ->assertOk()
            ->assertSeeText('Editar cuenta')
            ->assertSeeText('Carnet de identidad')
            ->assertSee('value="12345678"', false);
    }

    public function test_dashboard_shows_only_the_existing_sprint_one_sections_for_each_role(): void
    {
        $admin = $this->makeUser('admin', ['email' => 'admin@example.test']);
        $doctor = $this->makeUser('doctor', ['email' => 'doctor@example.test']);
        $patient = $this->makePatient('ana@example.test', '12345678');

        $adminDashboard = $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Panel de administración')
            ->assertSeeText('Usuarios y roles')
            ->assertSeeText('Directorio de pacientes');
        $this->assertSame(1, substr_count($adminDashboard->getContent(), 'aria-label="Navegación principal"'));

        $this->actingAs($doctor)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Panel médico')
            ->assertSeeText('Directorio de pacientes')
            ->assertDontSeeText('Usuarios y roles')
            ->assertDontSeeText('Citas Hoy');

        $this->actingAs($patient)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Panel del paciente')
            ->assertSeeText('Mi ficha personal')
            ->assertDontSeeText('Directorio de pacientes')
            ->assertDontSeeText('Usuarios y roles');
    }

    public function test_existing_internal_pages_share_the_navigation_and_active_section_for_each_role(): void
    {
        $admin = $this->makeUser('admin', ['email' => 'admin@example.test']);
        $doctor = $this->makeUser('doctor', ['email' => 'doctor@example.test']);
        $patient = $this->makePatient('ana@example.test', '12345678');

        $this->actingAs($doctor)
            ->get(route('patients.index'))
            ->assertOk()
            ->assertSee(route('dashboard'))
            ->assertSee(route('patients.index'))
            ->assertSee('aria-current="page"', false)
            ->assertDontSee(route('admin.users.index'));

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee(route('patients.index'))
            ->assertSee(route('admin.users.index'))
            ->assertSee('aria-current="page"', false);

        $this->actingAs($patient)
            ->get(route('patients.profile.edit'))
            ->assertOk()
            ->assertSee(route('dashboard'))
            ->assertSee(route('patients.profile.edit'))
            ->assertSee('aria-current="page"', false)
            ->assertDontSee(route('admin.users.index'));
    }

    public function test_administrator_cannot_remove_their_own_admin_role_or_delete_themselves(): void
    {
        $admin = $this->makeUser('admin', ['email' => 'admin@example.test']);
        $patientRole = $this->role('patient');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role_id' => $patientRole->id,
                'national_id' => '99887766',
                'birth_date' => '1988-01-24',
                'sex' => 'F',
            ])
            ->assertSessionHasErrors('role_id');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role_id' => $this->role('admin')->id]);
    }

    private function role(string $code): Role
    {
        $names = ['admin' => 'Administrador', 'doctor' => 'Médico', 'patient' => 'Paciente'];

        return Role::query()->firstOrCreate(['code' => $code], ['name' => $names[$code]]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeUser(string $role, array $attributes = []): User
    {
        return User::factory()->create([
            'role_id' => $this->role($role)->id,
            ...$attributes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function makePatient(string $email, string $nationalId, array $profile = []): User
    {
        $userAttributes = ['email' => $email];

        if (isset($profile['name'])) {
            $userAttributes['name'] = $profile['name'];
            unset($profile['name']);
        }

        $user = $this->makeUser('patient', $userAttributes);
        $user->patientProfile()->create([
            'national_id' => $nationalId,
            'birth_date' => '1995-06-12',
            'sex' => 'F',
            ...$profile,
        ]);

        return $user;
    }
}
