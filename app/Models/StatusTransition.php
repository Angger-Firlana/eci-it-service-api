<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StatusTransition
 * 
 * @property int $id
 * @property string $code
 * @property int $from_status_id
 * @property int $to_status_id
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Status $status
 * @property Collection|Role[] $roles
 *
 * @package App\Models
 */
class StatusTransition extends Model
{
	protected $table = 'status_transitions';

	protected $casts = [
		'from_status_id' => 'int',
		'to_status_id' => 'int'
	];

	protected $fillable = [
		'code',
		'from_status_id',
		'to_status_id',
		'description'
	];

	public function status()
	{
		return $this->belongsTo(Status::class, 'to_status_id');
	}

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'status_transition_roles')
					->withPivot('id')
					->withTimestamps();
	}
}
