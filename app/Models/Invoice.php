<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Invoice
 * 
 * @property int $id
 * @property string $invoice_number
 * @property int $service_request_id
 * @property Carbon $issue_date
 * @property Carbon $due_date
 * @property float $total_amount
 * @property int $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ServiceRequest $service_request
 * @property Status $status
 *
 * @package App\Models
 */
class Invoice extends Model
{
	protected $table = 'invoices';

	protected $casts = [
		'service_request_id' => 'int',
		'issue_date' => 'datetime',
		'due_date' => 'datetime',
		'total_amount' => 'float',
		'status_id' => 'int'
	];

	protected $fillable = [
		'invoice_number',
		'service_request_id',
		'issue_date',
		'due_date',
		'total_amount',
		'status_id'
	];

	public function service_request()
	{
		return $this->belongsTo(ServiceRequest::class);
	}

	public function status()
	{
		return $this->belongsTo(Status::class);
	}
}
