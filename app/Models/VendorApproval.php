<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class VendorApproval
 * 
 * @property int $id
 * @property int $approval_policy_id
 * @property int $approval_policy_step_id
 * @property int $service_request_id
 * @property int $approver_id
 * @property Carbon|null $approved_at
 * @property int $assigned_by
 * @property Carbon $assigned_at
 * @property int $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ApprovalPolicy $approval_policy
 * @property ApprovalPolicyStep $approval_policy_step
 * @property User $user
 * @property ServiceRequest $service_request
 * @property Status $status
 *
 * @package App\Models
 */
class VendorApproval extends Model
{
	protected $table = 'vendor_approvals';

	protected $casts = [
		'approval_policy_id' => 'int',
		'approval_policy_step_id' => 'int',
		'service_request_id' => 'int',
		'approver_id' => 'int',
		'approved_at' => 'datetime',
		'assigned_by' => 'int',
		'assigned_at' => 'datetime',
		'status_id' => 'int',
		'read_at' => 'datetime'
	];

	protected $fillable = [
		'approval_policy_id',
		'approval_policy_step_id',
		'service_request_id',
		'approver_id',
		'approved_at',
		'assigned_by',
		'assigned_at',
		'status_id',
		'read_at'
	];

	public function approval_policy()
	{
		return $this->belongsTo(ApprovalPolicy::class);
	}

	public function approval_policy_step()
	{
		return $this->belongsTo(ApprovalPolicyStep::class);
	}

	public function assigned_by()
	{
		return $this->belongsTo(User::class, 'assigned_by');
	}
	public function approver(){
		return $this->belongsTo(User::class, 'approver_id');
	}

	public function service_request()
	{
		return $this->belongsTo(ServiceRequest::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}
}
