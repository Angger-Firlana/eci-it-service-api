<?php

namespace App\Domains\SetEmailIt\Actions;

use App\Models\SetEmailIt;
use Illuminate\Database\Eloquent\Collection;

class GetSetEmailItList
{
    public function execute(): Collection
    {
        return SetEmailIt::with('user:id,name,username,email')->get();
    }
}
