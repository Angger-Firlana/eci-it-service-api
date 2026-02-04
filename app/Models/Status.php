<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Status
 * 
 * @property int $id
 * @property int $entity_type_id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property EntityType $entity_type
 * @property Collection|AuditLog[] $audit_logs
 * @property Collection|Invoice[] $invoices
 * @property Collection|ServiceRequest[] $service_requests
 * @property Collection|StatusTransition[] $status_transitions
 * @property Collection|VendorApproval[] $vendor_approvals
 *
 * @package App\Models
 */
class Status extends Model
{
	const PENDING = 1;


	protected $table = 'statuses';

	protected $casts = [
		'entity_type_id' => 'int'
	];

	protected $fillable = [
		'entity_type_id',
		'code',
		'name'
	];

	public static function forEntityCode(string $entityCode, string $statusCode): self
	{
		$status = self::where('code', $statusCode)
			->whereHas('entity_type', function ($query) use ($entityCode) {
				$query->where('code', $entityCode);
			})
			->first();

		if (!$status) {
			throw new \RuntimeException("Status {$statusCode} for {$entityCode} not found. Check StatusSeeder.");
		}

		return $status;
	}

	public static function idForEntityCode(string $entityCode, string $statusCode): int
	{
		return self::forEntityCode($entityCode, $statusCode)->id;
	}

	public function entity_type()
	{
		return $this->belongsTo(EntityType::class);
	}

	public function audit_logs()
	{
		return $this->hasMany(AuditLog::class, 'old_status_id');
	}

	public function invoices()
	{
		return $this->hasMany(Invoice::class);
	}

	public function service_requests()
	{
		return $this->hasMany(ServiceRequest::class);
	}

	public function status_transitions()
	{
		return $this->hasMany(StatusTransition::class, 'to_status_id');
	}

	public function vendor_approvals()
	{
		return $this->hasMany(VendorApproval::class);
	}
}
