<?php

namespace App\Domains\Vendor\Actions;

use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ListVendors
{
    public function execute(Request $request): LengthAwarePaginator
    {
        return Vendor::filter($request)->paginate($request->get('per_page', 15));
    }
}
