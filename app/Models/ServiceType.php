<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceType
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ServiceRequest[] $service_requests
 *
 * @package App\Models
 */
class ServiceType extends Model
{
	protected $table = 'service_types';

	protected $fillable = [
		'name'
	];

	public function service_requests()
	{
		return $this->hasMany(ServiceRequest::class);
	}
}
