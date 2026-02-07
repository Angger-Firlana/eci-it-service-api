<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Device
 * 
 * @property int $id
 * @property int $device_model_id
 * @property string $serial_number
 * @property bool $bad_asset
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property DeviceModel $device_model
 * @property Collection|ServiceRequestDetail[] $service_request_details
 *
 * @package App\Models
 */
class Device extends Model
{
	protected $table = 'devices';

	protected $casts = [
		'device_model_id' => 'int',
		'bad_asset' => 'bool'
	];

	protected $fillable = [
		'device_model_id',
		'serial_number',
		'bad_asset'
	];

	public function device_model()
	{
		return $this->belongsTo(DeviceModel::class);
	}

	public function service_request_details()
	{
		return $this->hasMany(ServiceRequestDetail::class);
	}
}
