<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CostType
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ServiceCost[] $service_costs
 *
 * @package App\Models
 */
class CostType extends Model
{
	protected $table = 'cost_types';

	protected $fillable = [
		'code',
		'name'
	];

	public function service_costs()
	{
		return $this->hasMany(ServiceCost::class);
	}
}
