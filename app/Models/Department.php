<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_departments')
                    ->using(UserDepartment::class)
                    ->withTimestamps();
    }
}
