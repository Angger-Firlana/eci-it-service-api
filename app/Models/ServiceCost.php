<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceCost
 * 
 * @property int $id
 * @property int $service_request_id
 * @property int $cost_type_id
 * @property float $amount
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property CostType $cost_type
 * @property ServiceRequest $service_request
 *
 * @package App\Models
 */
class ServiceCost extends Model
{
	protected $table = 'service_costs';

	protected $casts = [
		'service_request_id' => 'int',
		'cost_type_id' => 'int',
		'amount' => 'float'
	];

	protected $fillable = [
		'service_request_id',
		'cost_type_id',
		'amount',
		'description'
	];

	public function cost_type()
	{
		return $this->belongsTo(CostType::class);
	}

	public function service_request()
	{
		return $this->belongsTo(ServiceRequest::class);
	}
}
