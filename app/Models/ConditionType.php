<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ConditionType
 * 
 * @property int $id
 * @property int $condition_type_data_id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ConditionTypeDatum $condition_type_datum
 * @property Collection|ApprovalPolicy[] $approval_policies
 *
 * @package App\Models
 */
class ConditionType extends Model
{
	protected $table = 'condition_types';

	protected $casts = [
		'condition_type_data_id' => 'int'
	];

	protected $fillable = [
		'condition_type_data_id',
		'code',
		'name'
	];

	public function condition_type_datum()
	{
		return $this->belongsTo(ConditionTypeDatum::class, 'condition_type_data_id');
	}

	public function approval_policies()
	{
		return $this->hasMany(ApprovalPolicy::class);
	}
}
