<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Class ServiceRequest
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $admin_id
 * @property string $service_number
 * @property Carbon $request_date
 * @property Carbon|null $estimated_date
 * @property int $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 * @property Status $status
 * @property Collection|Invoice[] $invoices
 * @property Collection|ServiceCancellation[] $service_cancellations
 * @property Collection|ServiceCost[] $service_costs
 * @property Collection|ServiceLocation[] $service_locations
 * @property Collection|ServiceRequestDetail[] $service_request_details
 * @property Collection|VendorApproval[] $vendor_approvals
 *
 * @package App\Models
 */
class ServiceRequest extends Model
{
	protected $table = 'service_requests';

	protected $casts = [
		'user_id' => 'int',
		'admin_id' => 'int',
		'request_date' => 'datetime',
		'estimated_date' => 'datetime',
		'status_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'admin_id',
		'service_number',
		'request_date',
		'estimated_date',
		'status_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function admin()
	{
		return $this->belongsTo(User::class, 'admin_id', 'id');
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}

	public function invoices()
	{
		return $this->hasMany(Invoice::class);
	}

	public function service_cancellations()
	{
		return $this->hasMany(ServiceCancellation::class);
	}

	public function service_costs()
	{
		return $this->hasMany(ServiceCost::class);
	}

	public function service_locations()
	{
		return $this->hasMany(ServiceLocation::class);
	}

	public function service_request_details()
	{
		return $this->hasMany(ServiceRequestDetail::class);
	}

	public function vendor_approvals()
	{
		return $this->hasMany(VendorApproval::class);
	}

	public function scopeFilter($query, $request)
	{
		$equalFilters = [
			'user_id',
			'admin_id',
			'status_id',
		];

		foreach ($equalFilters as $field) {
			$query->when(
				$request->filled($field),
				fn ($q) => $q->where($field, $request->$field)
			);
		}

		return $query
			->when($request->filled('request_date'),
				fn ($q) => $q->whereDate('request_date', $request->request_date)
			)
			->when($request->filled('estimated_date'),
				fn ($q) => $q->whereDate('estimated_date', $request->estimated_date)
			)
			->when($request->filled('search'),
				fn ($q) => $q->where('service_number', 'like', "%{$request->search}%")
			);
	}


}
