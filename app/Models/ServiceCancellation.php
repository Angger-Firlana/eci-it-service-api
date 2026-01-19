<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceCancellation
 * 
 * @property int $id
 * @property int $service_request_id
 * @property int $cancelled_by
 * @property string $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 * @property ServiceRequest $service_request
 *
 * @package App\Models
 */
class ServiceCancellation extends Model
{
	protected $table = 'service_cancellations';

	protected $casts = [
		'service_request_id' => 'int',
		'cancelled_by' => 'int'
	];

	protected $fillable = [
		'service_request_id',
		'cancelled_by',
		'reason'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'cancelled_by');
	}

	public function service_request()
	{
		return $this->belongsTo(ServiceRequest::class);
	}
}
