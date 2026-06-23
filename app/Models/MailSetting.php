<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MailSetting
 *
 * Single-row table holding the application's outgoing (SMTP) mail configuration.
 * The password is stored encrypted at rest via the `encrypted` cast.
 *
 * @property int $id
 * @property string $mailer
 * @property string|null $host
 * @property int|null $port
 * @property string|null $username
 * @property string|null $password
 * @property string|null $encryption
 * @property string|null $from_address
 * @property string|null $from_name
 * @property bool $is_active
 */
class MailSetting extends Model
{
    protected $table = 'mail_settings';

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'is_active',
    ];

    protected $casts = [
        'port' => 'int',
        'password' => 'encrypted',
        'is_active' => 'bool',
    ];

    protected $hidden = [
        'password',
    ];
}
