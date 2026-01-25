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

	public function scopeFilter($query, $request)
	{
		return $query->when($request->has('code'), function ($query) use ($request) {
			return $query->where('code', 'like', '%' . $request->code . '%');
		})
		->when($request->has('name'), function ($query) use ($request) {
			return $query->where('name', 'like', '%' . $request->name . '%');
		});
	}
}
