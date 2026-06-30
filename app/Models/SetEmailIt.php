<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SetEmailIt
 *
 * @property int $id
 * @property int $user_id
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property User $user
 *
 * @package App\Models
 */
class SetEmailIt extends Model
{
    protected $table = 'set_email_it';

    protected $casts = [
        'user_id' => 'int',
        'is_active' => 'bool',
    ];

    protected $fillable = [
        'user_id',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
