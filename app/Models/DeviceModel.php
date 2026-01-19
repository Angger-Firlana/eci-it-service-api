<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceModel
 * 
 * @property int $id
 * @property int $device_type_id
 * @property string $brand
 * @property string $model
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property DeviceType $device_type
 * @property Collection|Device[] $devices
 *
 * @package App\Models
 */
class DeviceModel extends Model
{
	protected $table = 'device_models';

	protected $casts = [
		'device_type_id' => 'int'
	];

	protected $fillable = [
		'device_type_id',
		'brand',
		'model'
	];

	public function device_type()
	{
		return $this->belongsTo(DeviceType::class);
	}

	public function devices()
	{
		return $this->hasMany(Device::class);
	}
}
