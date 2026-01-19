<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ApprovalPolicy
 * 
 * @property int $id
 * @property int $entity_type_id
 * @property int $condition_type_id
 * @property string $condition_value
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ConditionType $condition_type
 * @property EntityType $entity_type
 * @property Collection|ApprovalPolicyStep[] $approval_policy_steps
 * @property Collection|VendorApproval[] $vendor_approvals
 *
 * @package App\Models
 */
class ApprovalPolicy extends Model
{
	protected $table = 'approval_policies';

	protected $casts = [
		'entity_type_id' => 'int',
		'condition_type_id' => 'int',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'entity_type_id',
		'condition_type_id',
		'condition_value',
		'is_active'
	];

	public function condition_type()
	{
		return $this->belongsTo(ConditionType::class);
	}

	public function entity_type()
	{
		return $this->belongsTo(EntityType::class);
	}

	public function approval_policy_steps()
	{
		return $this->hasMany(ApprovalPolicyStep::class);
	}

	public function vendor_approvals()
	{
		return $this->hasMany(VendorApproval::class);
	}
}
