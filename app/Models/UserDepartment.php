<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserDepartment extends Pivot
{
    protected $table = 'user_departments';

    public $incrementing = true; // Since we added an 'id' column in the migration

    protected $fillable = [
        'user_id',
        'department_id'
    ];
}
