<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceLocation
 * 
 * @property int $id
 * @property int $service_request_id
 * @property int|null $vendor_id
 * @property string $location_type
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ServiceRequest $service_request
 * @property Vendor|null $vendor
 *
 * @package App\Models
 */
class ServiceLocation extends Model
{
	protected $table = 'service_locations';

	protected $casts = [
		'service_request_id' => 'int',
		'vendor_id' => 'int',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'service_request_id',
		'vendor_id',
		'location_type',
		'address',
		'phone_number',
		'is_active'
	];

	public function service_request()
	{
		return $this->belongsTo(ServiceRequest::class);
	}

	public function vendor()
	{
		return $this->belongsTo(Vendor::class);
	}
}
