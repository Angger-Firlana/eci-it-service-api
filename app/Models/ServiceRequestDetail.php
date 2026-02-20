<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceRequestDetail
 * 
 * @property int $id
 * @property int $service_request_id
 * @property int|null $service_type_id
 * @property int $device_id
 * @property string $complaint
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Device $device
 * @property ServiceRequest $service_request
 * @property ServiceType|null $service_type
 *
 * @package App\Models
 */
class ServiceRequestDetail extends Model
{
	protected $table = 'service_request_details';

	protected $casts = [
		'service_request_id' => 'int',
		'device_id' => 'int',
		'solution' => 'string'
	];

	protected $fillable = [
		'service_request_id',
		'device_id',
		'complaint',
		'solution'
	];

	public function device()
	{
		return $this->belongsTo(Device::class);
	}

	public function service_request()
	{
		return $this->belongsTo(ServiceRequest::class);
	}

	public function complaint_images()
	{
		return $this->hasMany(ComplaintImage::class, 'service_request_detail_id', 'id');
	}
}
