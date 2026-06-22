<?php

namespace Tests\Feature;

use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Enums\VendorApprovalStatusCode;
use App\Models\ApprovalPolicy;
use App\Models\Department;
use App\Models\Role;
use App\Models\ServiceRequest;
use App\Models\Status;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorApproval;
use Database\Seeders\ApprovalPolicySeeder;
use Database\Seeders\ConditionTypeDataSeeder;
use Database\Seeders\ConditionTypeSeeder;
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

/**
 * Covers the two-phase vendor flow (Set Vendor -> wait for quote -> Set Biaya)
 * and the status parity between the user-facing list and the IT side.
 */
class VendorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $operatorUser;
    protected User $requesterUser;
    protected User $approverUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EntityTypeSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(StatusSeeder::class);
        $this->seed(StatusTransitionSeeder::class);
        $this->seed(ServiceTypeSeeder::class);
        $this->seed(CostTypeSeeder::class);
        $this->seed(ConditionTypeDataSeeder::class);
        $this->seed(ConditionTypeSeeder::class);
        $this->seed(ApprovalPolicySeeder::class);

        $this->operatorUser = $this->createUserWithRole('Operator Test', 'operator_test', 'operator@test.com', 'operator');
        $this->requesterUser = $this->createUserWithRole('Requester Test', 'requester_test', 'requester@test.com', 'user');
        $this->approverUser = $this->createUserWithRole('Approver Test', 'approver_test', 'approver@test.com', 'supervisor');
    }

    private function createRequestInVendorStage(): ServiceRequest
    {
        return ServiceRequest::create([
            'user_id' => $this->requesterUser->id,
            'operator_id' => $this->operatorUser->id,
            'service_number' => 'SR-VEN-' . uniqid(),
            'status_id' => Status::idForEntityCode('SERVICE_REQUEST', ServiceRequestStatusCode::REPAIR_IN_VENDOR),
        ]);
    }

    #[Test]
    public function it_exposes_the_set_vendor_transition_from_repair_in_vendor(): void
    {
        $serviceRequest = $this->createRequestInVendorStage();

        Sanctum::actingAs($this->operatorUser);
        $response = $this->getJson("/api/service-requests/{$serviceRequest->id}/allowed-transitions");

        $response->assertStatus(200);
        $this->assertTrue(
            collect($response->json('data.transitions'))->contains(fn ($t) => $t['code'] === 'SET_VENDOR'),
            'Operator should be allowed the SET_VENDOR transition from REPAIR_IN_VENDOR'
        );
    }

    #[Test]
    public function phase_one_set_vendor_moves_request_to_waiting_vendor_quote(): void
    {
        $serviceRequest = $this->createRequestInVendorStage();
        $vendor = Vendor::create([
            'name' => 'Vendor Sejahtera',
            'maps_url' => 'https://maps.google.com',
            'description' => 'Jl. Mawar No. 1',
        ]);

        Sanctum::actingAs($this->operatorUser);

        // Phase 1: assign the vendor (location only, no cost / approver yet).
        $this->postJson("/api/service-requests/{$serviceRequest->id}/locations", [
            'location_type' => 'external',
            'vendor_id' => $vendor->id,
            'address' => 'Jl. Mawar No. 1',
            'phone_number' => '08123456789',
            'is_active' => true,
        ])->assertStatus(201);

        // ...then move it to the new "waiting for the vendor's price" status.
        $this->putJson("/api/service-requests/{$serviceRequest->id}", [
            'status_code' => ServiceRequestStatusCode::WAITING_VENDOR_QUOTE->value,
            'log_notes' => 'Vendor diset, menunggu penawaran harga dari vendor',
        ])->assertStatus(200);

        $serviceRequest->refresh();
        $this->assertEquals(
            Status::idForEntityCode('SERVICE_REQUEST', ServiceRequestStatusCode::WAITING_VENDOR_QUOTE),
            $serviceRequest->status_id
        );

        // Parity: the owner's list must surface the exact same status code the IT
        // side derives from (WAITING_VENDOR_QUOTE -> "Menunggu Penawaran Vendor").
        Sanctum::actingAs($this->requesterUser);
        $list = $this->getJson(
            "/api/service-requests?user_id={$this->requesterUser->id}" .
            '&include=user,status,vendor_approvals,details.device.deviceModel.deviceType'
        );

        $list->assertStatus(200);
        $row = collect($list->json('data'))->firstWhere('id', $serviceRequest->id);
        $this->assertNotNull($row, 'Owner should see their own request in the list');
        $this->assertEquals('WAITING_VENDOR_QUOTE', $row['status']['code']);
        $this->assertSame([], $row['vendor_approvals'], 'No approvals should exist yet in phase 1');
    }

    #[Test]
    public function owner_list_includes_vendor_approvals_so_status_matches_it_side(): void
    {
        $serviceRequest = $this->createRequestInVendorStage();

        $policy = ApprovalPolicy::with('approval_policy_steps')->first();
        $this->assertNotNull($policy, 'An approval policy should be seeded');
        $step = $policy->approval_policy_steps->first();

        // Simulate the post-approval state: vendor approved, request back in
        // REPAIR_IN_VENDOR. The IT side derives "Perbaikan Vendor" from this; the
        // owner must receive the same approvals data to derive the same label.
        VendorApproval::create([
            'service_request_id' => $serviceRequest->id,
            'approver_id' => $this->approverUser->id,
            'assigned_by' => $this->operatorUser->id,
            'assigned_at' => now(),
            'approved_at' => now(),
            'status_id' => Status::idForEntityCode('VENDOR_APPROVAL', VendorApprovalStatusCode::APPROVED),
            'approval_policy_id' => $policy->id,
            'approval_policy_step_id' => $step->id,
        ]);

        Sanctum::actingAs($this->requesterUser);
        $list = $this->getJson(
            "/api/service-requests?user_id={$this->requesterUser->id}" .
            '&include=user,status,vendor_approvals,details.device.deviceModel.deviceType'
        );

        $list->assertStatus(200);
        $row = collect($list->json('data'))->firstWhere('id', $serviceRequest->id);
        $this->assertNotNull($row);
        $this->assertEquals('REPAIR_IN_VENDOR', $row['status']['code']);
        $this->assertCount(1, $row['vendor_approvals'], 'Owner list must include vendor approvals');
        $this->assertEquals('APPROVED', $row['vendor_approvals'][0]['status']['code']);
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
}
