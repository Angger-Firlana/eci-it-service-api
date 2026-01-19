<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StatusTransitionRole
 * 
 * @property int $id
 * @property int $status_transition_id
 * @property int $role_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Role $role
 * @property StatusTransition $status_transition
 *
 * @package App\Models
 */
class StatusTransitionRole extends Model
{
	protected $table = 'status_transition_roles';

	protected $casts = [
		'status_transition_id' => 'int',
		'role_id' => 'int'
	];

	protected $fillable = [
		'status_transition_id',
		'role_id'
	];

	public function role()
	{
		return $this->belongsTo(Role::class);
	}

	public function status_transition()
	{
		return $this->belongsTo(StatusTransition::class);
	}
}
