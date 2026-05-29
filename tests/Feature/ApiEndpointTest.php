<?php

namespace Tests\Feature;

use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Models\Department;
use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\DeviceType;
use App\Models\Role;
use App\Models\ServiceCancellation;
use App\Models\ServiceRequest;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\CostTypeSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\EntityTypeSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Database\Seeders\StatusSeeder;
use Database\Seeders\StatusTransitionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $operatorUser;
    protected User $requesterUser;
    protected string $adminToken;
    protected string $operatorToken;
    protected string $requesterToken;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('mail.admin_email', 'admin@example.com');
        config()->set('mail.manager_email', 'manager@example.com');

        $this->seed(EntityTypeSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(StatusSeeder::class);
        $this->seed(StatusTransitionSeeder::class);
        $this->seed(ServiceTypeSeeder::class);
        $this->seed(CostTypeSeeder::class);

        $this->adminUser = $this->createUserWithRole('Admin Test', 'admin_test', 'admin@test.com', 'admin');
        $this->operatorUser = $this->createUserWithRole('Operator Test', 'operator_test', 'operator@test.com', 'operator');
        $this->requesterUser = $this->createUserWithRole('Requester Test', 'requester_test', 'requester@test.com', 'user');

        $this->adminToken = $this->loginAndGetToken('admin_test');
        $this->operatorToken = $this->loginAndGetToken('operator_test');
        $this->requesterToken = $this->loginAndGetToken('requester_test');
    }

    #[Test]
    public function it_can_login(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'admin_test',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'code' => 200,
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'username', 'email'],
                ],
            ]);
    }

    #[Test]
    public function it_can_get_me(): void
    {
        $response = $this->withToken($this->adminToken)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'username' => 'admin_test',
                    'email' => 'admin@test.com',
                ],
            ]);
    }

    #[Test]
    public function it_can_manage_device_types(): void
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/device-type');
        $response->assertStatus(200)->assertJson(['success' => true]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/device-type', ['name' => 'Tablet']);
        $response->assertStatus(201);
        $typeId = $response->json('data.id');

        $this->withToken($this->adminToken)
            ->getJson("/api/device-type/{$typeId}")
            ->assertStatus(200);

        $this->withToken($this->adminToken)
            ->putJson("/api/device-type/{$typeId}", ['name' => 'Modern Tablet'])
            ->assertStatus(200);

        $this->withToken($this->adminToken)
            ->deleteJson("/api/device-type/{$typeId}")
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_manage_device_models(): void
    {
        $type = DeviceType::create(['name' => 'Smartphone']);

        $this->withToken($this->adminToken)
            ->getJson('/api/device-model')
            ->assertStatus(200);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/device-model', [
                'device_type_id' => $type->id,
                'brand' => 'Samsung',
                'model' => 'Galaxy S21',
            ]);
        $response->assertStatus(201);
        $modelId = $response->json('data.id');

        $this->withToken($this->adminToken)
            ->getJson("/api/device-model/{$modelId}")
            ->assertStatus(200);

        $this->withToken($this->adminToken)
            ->putJson("/api/device-model/{$modelId}", [
                'model' => 'Galaxy S22',
            ])
            ->assertStatus(200);

        $this->withToken($this->adminToken)
            ->deleteJson("/api/device-model/{$modelId}")
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_manage_devices(): void
    {
        $type = DeviceType::create(['name' => 'Printer']);
        $model = DeviceModel::create([
            'device_type_id' => $type->id,
            'brand' => 'HP',
            'model' => 'LaserJet',
        ]);

        $this->withToken($this->adminToken)
            ->getJson('/api/devices')
            ->assertStatus(200);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/devices', [
                'device_model_id' => $model->id,
                'serial_number' => 'SN999888777',
                'bad_asset' => false,
            ]);
        $response->assertStatus(201);
        $deviceId = $response->json('data.id');

        $this->withToken($this->adminToken)
            ->getJson("/api/devices/{$deviceId}")
            ->assertStatus(200);

        $this->withToken($this->adminToken)
            ->putJson("/api/devices/{$deviceId}", [
                'serial_number' => 'SN111222333',
                'bad_asset' => true,
            ])
            ->assertStatus(200);

        $this->withToken($this->adminToken)
            ->deleteJson("/api/devices/{$deviceId}")
            ->assertStatus(200);
    }

    #[Test]
    public function it_returns_allowed_transitions_for_the_authenticated_role(): void
    {
        $serviceRequest = ServiceRequest::create([
            'user_id' => $this->requesterUser->id,
            'operator_id' => $this->operatorUser->id,
            'service_number' => 'SR-TEST-001',
            'status_id' => Status::idForEntityCode('SERVICE_REQUEST', ServiceRequestStatusCode::REVIEW_IN_WORKSHOP),
        ]);

        $response = $this->withToken($this->operatorToken)
            ->getJson("/api/service-requests/{$serviceRequest->id}/allowed-transitions");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'service_request_id' => $serviceRequest->id,
                ],
            ]);

        $this->assertTrue(
            collect($response->json('data.transitions'))->contains(fn ($transition) => $transition['code'] === 'START_REPAIR_WORKSHOP')
        );
    }

    #[Test]
    public function it_allows_owner_create_and_admin_update_a_cancellation(): void
    {
        $serviceRequest = ServiceRequest::create([
            'user_id' => $this->requesterUser->id,
            'operator_id' => null,
            'service_number' => 'SR-CANCEL-001',
            'status_id' => Status::idForEntityCode('SERVICE_REQUEST', ServiceRequestStatusCode::REVIEW_IN_WORKSHOP),
        ]);

        $createResponse = $this->withToken($this->requesterToken)
            ->postJson("/api/service-requests/{$serviceRequest->id}/cancellation", [
                'reason' => 'No longer needed',
            ]);

        $createResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'service_request_id' => $serviceRequest->id,
                    'reason' => 'No longer needed',
                ],
            ]);

        $cancellationId = $createResponse->json('data.id');

        Sanctum::actingAs($this->adminUser);

        $this->putJson("/api/service-requests/{$serviceRequest->id}/cancellation/{$cancellationId}", [
                'reason' => 'Approved cancellation',
            ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $cancellationId,
                    'reason' => 'Approved cancellation',
                ],
            ]);
    }

    #[Test]
    public function it_marks_device_bad_asset_when_service_request_status_is_bad_asset(): void
    {
        $type = DeviceType::create(['name' => 'Laptop']);
        $model = DeviceModel::create([
            'device_type_id' => $type->id,
            'brand' => 'Dell',
            'model' => 'Latitude',
        ]);

        $device = Device::create([
            'device_model_id' => $model->id,
            'serial_number' => 'SNBADASSET001',
            'bad_asset' => false,
        ]);

        $createResponse = $this->withToken($this->requesterToken)
            ->postJson('/api/service-requests', [
                'details' => [
                    [
                        'device_id' => $device->id,
                        'device_type_id' => $type->id,
                        'complaint' => 'Cannot power on',
                    ],
                ],
            ]);

        $createResponse->assertStatus(200);
        $serviceRequestId = $createResponse->json('data.id');

        Sanctum::actingAs($this->operatorUser);

        $updateResponse = $this->putJson("/api/service-requests/{$serviceRequestId}", [
                'status_code' => ServiceRequestStatusCode::BAD_ASSET->value,
                'log_notes' => 'Not repairable',
            ]);

        $updateResponse->assertStatus(200);

        $device->refresh();
        $this->assertTrue($device->bad_asset);
    }

    #[Test]
    public function it_can_get_references(): void
    {
        $endpoints = [
            '/api/references/service-types',
            '/api/references/statuses',
            '/api/references/vendors',
            '/api/references/roles',
            '/api/references/departments',
            '/api/references/cost-types',
        ];

        foreach ($endpoints as $endpoint) {
            $this->withToken($this->adminToken)
                ->getJson($endpoint)
                ->assertStatus(200)
                ->assertJson(['success' => true]);
        }
    }

    #[Test]
    public function it_can_manage_departments(): void
    {
        $this->withToken($this->adminToken)
            ->getJson('/api/departments')
            ->assertStatus(200);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/departments', ['name' => 'Marketing', 'code' => 'MKT']);
        $response->assertStatus(201);
        $departmentId = $response->json('data.id');

        $this->withToken($this->adminToken)
            ->putJson("/api/departments/{$departmentId}", ['name' => 'Digital Marketing'])
            ->assertStatus(200);

        $this->withToken($this->adminToken)
            ->deleteJson("/api/departments/{$departmentId}")
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_logout(): void
    {
        $this->withToken($this->adminToken)
            ->postJson('/api/auth/logout')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful',
            ]);
    }

    private function createUserWithRole(string $name, string $username, string $email, string $roleName): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $department = Department::where('code', 'IT')->firstOrFail();

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('password'),
            'pin' => '123456',
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id);
        $user->departments()->attach($department->id);

        return $user;
    }

    private function loginAndGetToken(string $username): string
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => $username,
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        return $response->json('data.token');
    }
}
