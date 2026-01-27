<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\DeviceType;
use App\Models\DeviceModel;
use App\Models\Device;
use Illuminate\Support\Facades\Hash;

class ApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic data
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\DepartmentSeeder::class);
        $this->seed(\Database\Seeders\StatusSeeder::class);
        $this->seed(\Database\Seeders\ServiceTypeSeeder::class);
        $this->seed(\Database\Seeders\CostTypeSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();
        $itDept = Department::where('code', 'IT')->first();

        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'pin' => '123456'
        ]);

        $this->adminUser->roles()->attach($adminRole->id);
        $this->adminUser->departments()->attach($itDept->id);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token');
    }

    /** @test */
    public function it_can_login()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'code',
                'message',
                'data' => ['token']
            ]);
    }

    /** @test */
    public function it_can_register()
    {
        $userRole = Role::where('name', 'user')->first();
        
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'role_id' => $userRole->id
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status', 'code', 'message', 'data' => ['id', 'name', 'email']
            ]);
    }

    /** @test */
    public function it_can_get_me()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'email' => 'admin@test.com'
                ]
            ]);
    }

    /** @test */
    public function it_can_manage_device_types()
    {
        // Index
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/device-type');
        $response->assertStatus(200);

        // Store
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/device-type', ['name' => 'Tablet']);
        $response->assertStatus(201);
        $typeId = $response->json('data.id');

        // Show
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/device-type/{$typeId}");
        $response->assertStatus(200);

        // Update
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/device-type/{$typeId}", ['name' => 'Modern Tablet']);
        $response->assertStatus(200);

        // Delete
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/device-type/{$typeId}");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_manage_device_models()
    {
        $type = DeviceType::create(['name' => 'Smartphone']);

        // Index
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/device-model');
        $response->assertStatus(200);

        // Store
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/device-model', [
                'device_type_id' => $type->id,
                'brand' => 'Samsung',
                'model' => 'Galaxy S21'
            ]);
        $response->assertStatus(201);
        $modelId = $response->json('data.id');

        // Show
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/device-model/{$modelId}");
        $response->assertStatus(200);

        // Update
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/device-model/{$modelId}", [
                'brand' => 'Samsung',
                'model' => 'Galaxy S22'
            ]);
        $response->assertStatus(200);

        // Delete
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/device-model/{$modelId}");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_manage_devices()
    {
        $type = DeviceType::create(['name' => 'Printer']);
        $model = DeviceModel::create([
            'device_type_id' => $type->id,
            'brand' => 'HP',
            'model' => 'LaserJet'
        ]);

        // Index
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/devices');
        $response->assertStatus(200);

        // Store
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/devices', [
                'device_model_id' => $model->id,
                'serial_number' => 'SN999888777'
            ]);
        $response->assertStatus(201);
        $deviceId = $response->json('data.id');

        // Show
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/devices/{$deviceId}");
        $response->assertStatus(200);

        // Update
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/devices/{$deviceId}", [
                'serial_number' => 'SN111222333'
            ]);
        $response->assertStatus(200);

        // Delete
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/devices/{$deviceId}");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_get_references()
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
            $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->getJson($endpoint);
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function it_can_manage_departments()
    {
        // Index
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/departments');
        $response->assertStatus(200);

        // Store
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/departments', ['name' => 'Marketing', 'code' => 'MKT']);
        $response->assertStatus(201);
        $deptId = $response->json('data.id');

        // Update
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/departments/{$deptId}", ['name' => 'Digital Marketing', 'code' => 'DMKT']);
        $response->assertStatus(200);

        // Delete
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/departments/{$deptId}");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_logout()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }
}
