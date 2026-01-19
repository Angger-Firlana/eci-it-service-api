<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceType
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|DeviceModel[] $device_models
 *
 * @package App\Models
 */
class DeviceType extends Model
{
	protected $table = 'device_types';

	protected $fillable = [
		'name'
	];

	public function device_models()
	{
		return $this->hasMany(DeviceModel::class);
	}
}
