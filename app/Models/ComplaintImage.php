<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintImage extends Model
{
    //
    protected $fillable = [
        'service_request_detail_id',
        'image_path',
    ];

    public function serviceRequestDetail()
    {
        return $this->belongsTo(ServiceRequestDetail::class);
    }
}
