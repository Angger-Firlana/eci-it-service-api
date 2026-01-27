<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ApprovalPolicyStep[] $approval_policy_steps
 * @property Collection|StatusTransition[] $status_transitions
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Role extends Model
{
	const ADMIN = 1;
	const USER = 2;


	protected $table = 'roles';

	protected $fillable = [
		'name'
	];

	public function approval_policy_steps()
	{
		return $this->hasMany(ApprovalPolicyStep::class);
	}

	public function status_transitions()
	{
		return $this->belongsToMany(StatusTransition::class, 'status_transition_roles')
					->withPivot('id')
					->withTimestamps();
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'user_roles')
					->withPivot('id')
					->withTimestamps();
	}
}
