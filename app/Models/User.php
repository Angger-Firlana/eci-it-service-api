<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $pin
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|AuditLog[] $audit_logs
 * @property Collection|Notification[] $notifications
 * @property Collection|ServiceCancellation[] $service_cancellations
 * @property Collection|ServiceRequest[] $service_requests
 * @property Collection|Role[] $roles
 * @property Collection|VendorApproval[] $vendor_approvals
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;
    
	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime'
	];

	protected $hidden = [
		'password',
		'pin',
		'remember_token'
	];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'pin',
        'remember_token',
    ];

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'user_departments')
                    ->using(UserDepartment::class)
                    ->withTimestamps();
    }

	public function audit_logs()
	{
		return $this->hasMany(AuditLog::class, 'actor_id');
	}

	public function notifications()
	{
		return $this->hasMany(Notification::class);
	}

	public function service_cancellations()
	{
		return $this->hasMany(ServiceCancellation::class, 'cancelled_by');
	}

	public function service_requests()
	{
		return $this->hasMany(ServiceRequest::class);
	}

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'user_roles')
					->withPivot('id')
					->withTimestamps();
	}

	public function vendor_approvals()
	{
		return $this->hasMany(VendorApproval::class, 'assigned_by');
	}
}
