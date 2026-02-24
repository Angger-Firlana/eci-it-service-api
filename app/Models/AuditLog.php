<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuditLog
 * 
 * @property int $id
 * @property int $actor_id
 * @property int $entity_id
 * @property int $entity_type_id
 * @property string $action
 * @property string $notes
 * @property int $old_status_id
 * @property int $new_status_id
 * @property Carbon $created_at
 * 
 * @property User $user
 * @property EntityType $entity_type
 * @property Status $status
 *
 * @package App\Models
 */
class AuditLog extends Model
{
	protected $table = 'audit_logs';
	public $timestamps = true;

	protected $casts = [
		'actor_id' => 'int',
		'entity_id' => 'int',
		'entity_type_id' => 'int',
		'old_status_id' => 'int',
		'new_status_id' => 'int'
	];

	protected $fillable = [
		'actor_id',
		'entity_id',
		'entity_type_id',
		'action',
		'notes',
		'old_status_id',
		'new_status_id'
	];

	public function actor()
	{
		return $this->belongsTo(User::class, 'actor_id');
	}

	public function entity_type()
	{
		return $this->belongsTo(EntityType::class);
	}

	public function old_status()
	{
		return $this->belongsTo(Status::class, 'old_status_id');
	}

	public function new_status()
	{
		return $this->belongsTo(Status::class, 'new_status_id');
	}
}
