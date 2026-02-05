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
		$equalFilters = ['user_id', 'admin_id', 'status_id'];

		foreach ($equalFilters as $field) {
			$query->when(
				$request->filled($field),
				fn ($q) => $q->where("service_requests.$field", $request->$field)
			);
		}

		$query
			->when(
				$request->filled('request_date'),
				fn ($q) => $q->whereDate('service_requests.request_date', $request->request_date)
			)
			->when(
				$request->filled('estimated_date'),
				fn ($q) => $q->whereDate('service_requests.estimated_date', $request->estimated_date)
			)
			->select('service_requests.*');

		if ($request->filled('keyword')) {
			$keyword = addcslashes($request->keyword, '%_');

			$query
				->leftJoin('users as u', 'service_requests.user_id', '=', 'u.id')
				->leftJoin('service_request_details as srd', 'service_requests.id', '=', 'srd.service_request_id')
				->leftJoin('devices as d', 'srd.device_id', '=', 'd.id')
				->leftJoin('device_models as dm', 'd.device_model_id', '=', 'dm.id')
				->leftJoin('service_locations as sl', 'd.service_location_id', '=', 'sl.id')
				->leftJoin('vendors as v', 'sl.vendor_id', '=', 'v.id')
				->where(function ($q) use ($keyword) {
					$q->where('u.name', 'like', "%{$keyword}%")
					->orWhere('dm.name', 'like', "%{$keyword}%")
					->orWhere('d.serial_number', 'like', "%{$keyword}%")
					->orWhere('v.name', 'like', "%{$keyword}%")
					->orWhere('sl.phone_number', 'like', "%{$keyword}%");	
				})
				->distinct();
		}

		return $query;
	}

}
