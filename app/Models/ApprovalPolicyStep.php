<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ApprovalPolicyStep
 * 
 * @property int $id
 * @property int $approval_policy_id
 * @property int $step_order
 * @property int $role_id
 * @property bool $is_mandatory
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ApprovalPolicy $approval_policy
 * @property Role $role
 * @property Collection|VendorApproval[] $vendor_approvals
 *
 * @package App\Models
 */
class ApprovalPolicyStep extends Model
{
	protected $table = 'approval_policy_steps';

	protected $casts = [
		'approval_policy_id' => 'int',
		'step_order' => 'int',
		'role_id' => 'int',
		'is_mandatory' => 'bool'
	];

	protected $fillable = [
		'approval_policy_id',
		'step_order',
		'role_id',
		'is_mandatory'
	];

	public function approval_policy()
	{
		return $this->belongsTo(ApprovalPolicy::class);
	}

	public function role()
	{
		return $this->belongsTo(Role::class);
	}

	public function vendor_approvals()
	{
		return $this->hasMany(VendorApproval::class);
	}
}
