<?php

namespace Tests\Feature;

use App\Models\Pole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PjuApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_read_snapshot_data(): void
    {
        $user = $this->seedUser('user');
        Pole::query()->create([
            ...$this->polePayload(),
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'user@pju.local');

        $this->withToken($token)
            ->getJson('/api/poles')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.created_by.email', 'user@pju.local');

        $this->withToken($token)
            ->getJson('/api/poles?query=sudah ada')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kwh_meter', 'sudah_ada');
    }

    public function test_map_markers_can_be_limited_to_visible_bounds(): void
    {
        $user = $this->seedUser('user');
        Pole::query()->create([
            ...$this->polePayload('PEL-TST-001'),
            'latitude' => -7.965,
            'longitude' => 110.603,
        ]);
        Pole::query()->create([
            ...$this->polePayload('PEL-TST-002'),
            'latitude' => -7.966,
            'longitude' => 110.604,
        ]);
        Pole::query()->create([
            ...$this->polePayload('PEL-TST-003'),
            'latitude' => -6.21462,
            'longitude' => 106.82137,
        ]);

        $this->actingAs($user)
            ->getJson('/peta/markers?north=-7.960&south=-7.970&east=110.610&west=110.600&limit=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('meta.limited', true)
            ->assertJsonPath('meta.limit', 1);
    }

    public function test_poles_can_be_created_and_updated(): void
    {
        Storage::fake('public');

        $surveyor = $this->seedUser('surveyor');
        $superadmin = $this->seedUser('superadmin');
        $token = $surveyor->createToken('test')->plainTextToken;

        $created = $this->withToken($token)
            ->post('/api/poles', [
                ...$this->polePayload(),
                'photo' => UploadedFile::fake()->image('pole.jpg'),
            ])
            ->assertCreated()
            ->assertJsonPath('data.customer_id', 'PEL-TST-001')
            ->assertJsonPath('data.created_by.email', 'surveyor@pju.local')
            ->assertJsonPath('data.updated_by.email', 'surveyor@pju.local');

        $this->assertDatabaseCount('daily_reports', 1);
        $this->assertDatabaseHas('poles', [
            'id' => $created->json('data.id'),
            'created_by_id' => $surveyor->id,
            'updated_by_id' => $surveyor->id,
        ]);
        $this->assertNotEmpty(Storage::disk('public')->files('poles'));

        Sanctum::actingAs($superadmin);

        $this->flushHeaders()
            ->patchJson('/api/poles/'.$created->json('data.id'), [
                ...$this->polePayload(),
                'condition' => 'berkarat',
            ])
            ->assertOk()
            ->assertJsonPath('data.condition', 'berkarat')
            ->assertJsonPath('data.created_by.email', 'surveyor@pju.local')
            ->assertJsonPath('data.updated_by.email', 'superadmin@pju.local');

        $this->assertDatabaseHas('poles', [
            'id' => $created->json('data.id'),
            'created_by_id' => $surveyor->id,
            'updated_by_id' => $superadmin->id,
        ]);

        $this->assertDatabaseCount('daily_reports', 2);
    }

    public function test_sub_names_are_generated_from_existing_sources(): void
    {
        $surveyor = $this->seedUser('surveyor');
        $token = $surveyor->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/poles', $this->polePayload())
            ->assertCreated();

        $this->withToken($token)
            ->postJson('/api/poles', [
                ...$this->polePayload(),
                'route_type' => 'sub',
            ])
            ->assertCreated()
            ->assertJsonPath('data.customer_id', 'PEL-TST-001')
            ->assertJsonPath('data.sub_name', '001');

        $this->withToken($token)
            ->postJson('/api/poles', [
                ...$this->polePayload(),
                'route_type' => 'sub',
            ])
            ->assertCreated()
            ->assertJsonPath('data.customer_id', 'PEL-TST-001')
            ->assertJsonPath('data.sub_name', '002');

        $this->withToken($token)
            ->postJson('/api/poles', [
                ...$this->polePayload('PEL-TST-404'),
                'route_type' => 'sub',
            ])
            ->assertUnprocessable();
    }

    public function test_source_can_be_changed_to_sub_with_generated_number(): void
    {
        $surveyor = $this->seedUser('surveyor');
        $token = $surveyor->createToken('test')->plainTextToken;

        $sourceToConvert = $this->withToken($token)
            ->postJson('/api/poles', $this->polePayload('PEL-TST-001'))
            ->assertCreated();

        $this->withToken($token)
            ->patchJson('/api/poles/'.$sourceToConvert->json('data.id'), [
                ...$this->polePayload('PEL-TST-001'),
                'route_type' => 'sub',
            ])
            ->assertOk()
            ->assertJsonPath('data.customer_id', 'PEL-TST-001')
            ->assertJsonPath('data.sub_name', '001');

        $this->withToken($token)
            ->postJson('/api/poles', [
                ...$this->polePayload('PEL-TST-001'),
                'route_type' => 'sub',
            ])
            ->assertCreated()
            ->assertJsonPath('data.sub_name', '002');
    }

    public function test_damage_report_uploads_photo_and_marks_pole_as_damaged(): void
    {
        Storage::fake('public');

        $user = $this->seedUser('surveyor');
        $token = $user->createToken('test')->plainTextToken;
        $pole = Pole::query()->create([
            ...$this->polePayload(),
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
        ]);

        $this->withToken($token)
            ->post('/api/daily-reports', [
                'pole_id' => $pole->id,
                'notes' => 'Lampu mati total.',
                'photo' => UploadedFile::fake()->image('damage.jpg'),
            ])
            ->assertCreated()
            ->assertJsonPath('data.notes', 'Lampu mati total.');

        $this->assertSame('keropos', $pole->fresh()->condition);
        $this->assertSame($user->id, $pole->fresh()->updated_by_id);
        $this->assertNotEmpty(Storage::disk('public')->files('reports'));
    }

    public function test_pole_can_be_deleted(): void
    {
        $user = $this->seedUser('surveyor');
        $token = $user->createToken('test')->plainTextToken;
        $pole = Pole::query()->create([
            ...$this->polePayload(),
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
        ]);

        $this->withToken($token)
            ->deleteJson('/api/poles/'.$pole->id)
            ->assertOk()
            ->assertJsonPath('message', 'Data tiang berhasil dihapus.');

        $this->assertDatabaseMissing('poles', ['id' => $pole->id]);
    }

    public function test_invalid_login_is_rejected(): void
    {
        $this->seedUser('surveyor');

        $this->postJson('/api/login', [
            'email' => 'surveyor@pju.local',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    public function test_authenticated_user_can_update_profile_and_password(): void
    {
        Storage::fake('public');

        $user = $this->seedUser('surveyor');
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->post('/api/me/profile', [
                'name' => 'Dimas Updated',
                'email' => 'dimas.updated@pju.local',
                'phone' => '081234567890',
                'address' => 'Wonosari',
                'photo' => UploadedFile::fake()->image('profile.jpg'),
            ])
            ->assertOk()
            ->assertJsonPath('user.name', 'Dimas Updated')
            ->assertJsonPath('user.phone', '081234567890');

        $this->assertNotEmpty(Storage::disk('public')->files('profiles'));

        $this->withToken($token)
            ->postJson('/api/me/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertOk();

        $this->postJson('/api/login', [
            'email' => 'dimas.updated@pju.local',
            'password' => 'new-password',
        ])->assertOk();
    }

    public function test_user_role_is_read_only(): void
    {
        $user = $this->seedUser('user');
        $pole = Pole::query()->create([
            ...$this->polePayload(),
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/poles')->assertOk();
        $this->withToken($token)->getJson('/api/daily-reports')->assertOk();

        $this->withToken($token)->postJson('/api/poles', $this->polePayload('PEL-TST-002'))->assertForbidden();
        $this->withToken($token)->patchJson('/api/poles/'.$pole->id, $this->polePayload())->assertForbidden();
        $this->withToken($token)->deleteJson('/api/poles/'.$pole->id)->assertForbidden();
        $this->withToken($token)->postJson('/api/daily-reports', [
            'pole_id' => $pole->id,
            'notes' => 'Lampu mati total.',
        ])->assertForbidden();
    }

    public function test_verifikator_can_update_delete_and_verify_poles_but_cannot_create_or_manage_users(): void
    {
        $surveyor = $this->seedUser('surveyor');
        $verifikator = $this->seedUser('verifikator');
        $token = $verifikator->createToken('test')->plainTextToken;
        $pole = Pole::query()->create([
            ...$this->polePayload(),
            'created_by_id' => $surveyor->id,
            'updated_by_id' => $surveyor->id,
        ]);

        $this->withToken($token)
            ->postJson('/api/poles', $this->polePayload('PEL-TST-002'))
            ->assertForbidden();

        $this->withToken($token)
            ->patchJson('/api/poles/'.$pole->id, [
                ...$this->polePayload(),
                'condition' => 'berkarat',
            ])
            ->assertOk()
            ->assertJsonPath('data.condition', 'berkarat')
            ->assertJsonPath('data.updated_by.email', 'verifikator@pju.local');

        $this->withToken($token)
            ->patchJson('/api/poles/'.$pole->id.'/verification', ['is_verified' => true])
            ->assertOk()
            ->assertJsonPath('data.is_verified', true)
            ->assertJsonPath('data.verified_by.email', 'verifikator@pju.local');

        $this->withToken($token)
            ->postJson('/api/poles/'.$pole->id.'/verification', ['is_verified' => false])
            ->assertOk()
            ->assertJsonPath('data.is_verified', false)
            ->assertJsonPath('data.verified_by', null);

        $this->assertDatabaseHas('poles', [
            'id' => $pole->id,
            'is_verified' => false,
            'verified_by_id' => null,
        ]);

        $this->withToken($token)->getJson('/api/users')->assertForbidden();
        $this->withToken($token)->deleteJson('/api/poles/'.$pole->id)->assertOk();
        $this->assertDatabaseMissing('poles', ['id' => $pole->id]);
    }

    public function test_superadmin_can_manage_users(): void
    {
        $superadmin = $this->seedUser('superadmin');
        $token = $superadmin->createToken('test')->plainTextToken;

        $created = $this->withToken($token)
            ->postJson('/api/users', [
                'name' => 'Viewer Baru',
                'email' => 'viewerbaru@pju.local',
                'role' => 'verifikator',
                'password' => 'password',
            ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'verifikator');

        $this->withToken($token)
            ->patchJson('/api/users/'.$created->json('data.id'), [
                'name' => 'Surveyor Baru',
                'email' => 'surveyorbaru@pju.local',
                'role' => 'surveyor',
                'password' => '',
            ])
            ->assertOk()
            ->assertJsonPath('data.role', 'surveyor');

        $this->withToken($token)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->withToken($token)
            ->deleteJson('/api/users/'.$created->json('data.id'))
            ->assertOk();

        $this->assertDatabaseMissing('users', ['email' => 'surveyorbaru@pju.local']);
    }

    public function test_surveyor_cannot_manage_users(): void
    {
        $surveyor = $this->seedUser('surveyor');
        $token = $surveyor->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/users')->assertForbidden();
        $this->withToken($token)->postJson('/api/users', [
            'name' => 'Viewer Baru',
            'email' => 'viewerbaru@pju.local',
            'role' => 'user',
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_last_superadmin_and_self_delete_are_protected(): void
    {
        $superadmin = $this->seedUser('superadmin');
        $token = $superadmin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->deleteJson('/api/users/'.$superadmin->id)
            ->assertUnprocessable();

        $this->withToken($token)
            ->patchJson('/api/users/'.$superadmin->id, [
                'name' => 'Superadmin PJU',
                'email' => 'superadmin@pju.local',
                'role' => 'surveyor',
                'password' => '',
            ])
            ->assertUnprocessable();
    }

    private function seedUser(string $role): User
    {
        return User::query()->create([
            'name' => match ($role) {
                'superadmin' => 'Superadmin PJU',
                'user' => 'Viewer PJU',
                'verifikator' => 'Verifikator PJU',
                default => 'Dimas Pratama',
            },
            'role' => $role,
            'email' => match ($role) {
                'superadmin' => 'superadmin@pju.local',
                'user' => 'user@pju.local',
                'verifikator' => 'verifikator@pju.local',
                default => 'surveyor@pju.local',
            },
            'password' => Hash::make('password'),
        ]);
    }

    private function polePayload(string $customerId = 'PEL-TST-001'): array
    {
        return [
            'customer_id' => $customerId,
            'street_name' => 'Jalan Test',
            'latitude' => -6.21462,
            'longitude' => 106.82137,
            'power_usage' => 90,
            'condition' => 'kokoh',
            'pole_type' => 'tiang_baja_galvanis',
            'height' => 7,
            'ornament_type' => 'single_arm',
            'lamp_type' => 'led',
            'lamp_status' => 'menyala',
            'lamp_house_type' => 'caping',
            'lamp_house_condition' => 'baik',
            'light_point_count' => 1,
            'kwh_meter' => 'sudah_ada',
            'ownership' => 'dpu_kp',
            'notes' => 'Catatan test.',
        ];
    }
}
