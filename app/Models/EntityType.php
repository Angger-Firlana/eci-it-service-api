<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EntityType
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ApprovalPolicy[] $approval_policies
 * @property Collection|AuditLog[] $audit_logs
 * @property Collection|Status[] $statuses
 *
 * @package App\Models
 */
class EntityType extends Model
{
	protected $table = 'entity_types';

	protected $fillable = [
		'code',
		'name'
	];

	public function approval_policies()
	{
		return $this->hasMany(ApprovalPolicy::class);
	}

	public function audit_logs()
	{
		return $this->hasMany(AuditLog::class);
	}

	public function statuses()
	{
		return $this->hasMany(Status::class);
	}
}
