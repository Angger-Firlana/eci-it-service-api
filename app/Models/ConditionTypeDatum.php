<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ConditionTypeDatum
 * 
 * @property int $id
 * @property string $type_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ConditionType[] $condition_types
 *
 * @package App\Models
 */
class ConditionTypeDatum extends Model
{
	protected $table = 'condition_type_data';

	protected $fillable = [
		'type_data'
	];

	public function condition_types()
	{
		return $this->hasMany(ConditionType::class, 'condition_type_data_id');
	}
}
