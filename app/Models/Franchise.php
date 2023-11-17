<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use App\Services\Database\Eloquent\User as BaseUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class Franchise extends BaseUser
{
    use Notifiable, SoftDeletes;

    protected $table = 'franchises';

    protected $fillable = [
        'distribution_center_id', 'code', 'name', 'username', 'email', 'email_verified_at',
        'landline_number', 'phone_number', 'location', 'address',
        'password', 'locale', 'approval_date', 'fo_approval_date',
        'offering_letter_reference_number', 'fo_offering_letter_reference_number',
        'issuance_number', 'fo_issuance_number', 'status',
        'transfer_to_virtual_account_bank_name', 'transfer_to_virtual_account_number',
        'npwp',
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
        return $this->hasMany(FranchiseAccessToken::class, 'user_id', 'id');
    }

    public function distributionCenter()
    {
        return $this->belongsTo(DistributionCenter::class, 'distribution_center_id', 'id', 'distributionCenter');
    }
}
