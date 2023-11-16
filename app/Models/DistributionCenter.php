<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use App\Services\Database\Eloquent\User as BaseUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionCenter extends BaseUser
{
    use Notifiable, SoftDeletes;

    protected $table = 'distribution_centers';

    protected $fillable = [
        'code', 'name', 'username', 'email', 'email_verified_at',
        'landline_number', 'phone_number', 'location', 'address',
        'password', 'locale', 'approval_date', 'fo_approval_date',
        'offering_letter_reference_number', 'fo_offering_letter_reference_number',
        'issuance_number', 'fo_issuance_number', 'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'approval_date' => 'date',
        'fo_approval_date' => 'date',
    ];

    public function tokens()
    {
        return $this->hasMany(DistributionCenterAccessToken::class, 'user_id', 'id');
    }

    public function franchises()
    {
        return $this->hasMany(Franchise::class, 'distribution_center_id', 'id');
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'distribution_center_id', 'id');
    }
}
