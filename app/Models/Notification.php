<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Notification
 * 
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $message
 * @property Carbon $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Notification extends Model
{
	protected $table = 'notifications';

	protected $casts = [
		'user_id' => 'int',
		'read_at' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'service_request_id',
		'title',
		'message',
		'read_at'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function service_request(){
		return $this->belongsTo(ServiceRequest::class);
	}
}
