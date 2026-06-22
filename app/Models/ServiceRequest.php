<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceRequest
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $operator_id
 * @property string $service_number
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

	public static function availableIncludes(): array
	{
		return [
			'user' => [
				'user:id,name,username,email',
			],
			'operator' => [
				'operator:id,name,username,email',
			],
			'status' => [
				'status:id,name,code',
			],
			'vendor_approvals' => [
				'vendor_approvals:id,service_request_id,approver_id,status_id',
				'vendor_approvals.status:id,name,code',
			],
			'locations' => [
				'service_locations:id,service_request_id,vendor_id,location_type,address,phone_number,is_active',
			],
			'details.device.deviceModel' => [
				'service_request_details.device.device_model:id,device_type_id,brand,model',
			],
			'details.device.deviceModel.deviceType' => [
				'service_request_details:id,service_request_id,device_id,device_type_id,complaint,solution',
				'service_request_details.device:id,device_model_id,serial_number,bad_asset',
				'service_request_details.device.device_model:id,device_type_id,brand,model',
				'service_request_details.device.device_model.device_type:id,name',
			],
			'locations(active)' => [
				'service_locations' => function ($query) {
					$query->where('is_active', true)
						->select([
							'id',
							'service_request_id',
							'vendor_id',
							'location_type',
							'address',
							'phone_number',
							'is_active'
						]);
				},
			],
		];
	}

	protected $casts = [
		'user_id' => 'int',
		'operator_id' => 'int',
		'status_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'operator_id',
		'service_number',
		'status_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function operator()
	{
		return $this->belongsTo(User::class, 'operator_id', 'id');
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
		$equalFilters = ['user_id', 'operator_id', 'status_id'];

		foreach ($equalFilters as $field) {
			$query->when(
				$request->filled($field),
				fn ($q) => $q->where("service_requests.$field", $request->$field)
			);
		}

		$query
			->when(
				$request->filled('request_date'),
				fn ($q) => $q->whereDate('service_requests.created_at', $request->request_date)
			)
			->select([
				'service_requests.id',
				'service_requests.user_id',
				'service_requests.operator_id',
				'service_requests.status_id',
				'service_requests.service_number',
				'service_requests.created_at'
			]);

		if ($request->filled('search')) {
			$keyword = addcslashes($request->search, '%_');

			$query
				->leftJoin('users as u', 'service_requests.user_id', '=', 'u.id')
				->leftJoin('service_request_details as srd', 'service_requests.id', '=', 'srd.service_request_id')
				->leftJoin('devices as d', 'srd.device_id', '=', 'd.id')
				->leftJoin('device_models as dm', 'd.device_model_id', '=', 'dm.id')
				->leftJoin('service_locations as sl', 'service_requests.id', '=', 'sl.service_request_id')
				->leftJoin('vendors as v', 'sl.vendor_id', '=', 'v.id')
				->where(function ($q) use ($keyword) {
					$q->where('u.name', 'like', "%{$keyword}%")
					->orWhere('service_requests.service_number', 'like', "%{$keyword}%")
					->orWhere('dm.model', 'like', "%{$keyword}%")
					->orWhere('d.serial_number', 'like', "%{$keyword}%")
					->orWhere('v.name', 'like', "%{$keyword}%")
					->orWhere('sl.phone_number', 'like', "%{$keyword}%");	
				})
				->distinct();
		}

		return $query;
	}

}
