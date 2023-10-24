<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    protected $table = 'stores';

    protected $fillable = [
        'distribution_center_id', 'code', 'name', 'email',
        'landline_number', 'phone_number', 'location', 'address',
        'approval_date', 'fo_approval_date', 'offering_letter_reference_number',
        'fo_offering_letter_reference_number', 'issuance_number', 'fo_issuance_number',
        'status',
    ];

    protected $casts = [
        'approval_date' => 'date',
        'fo_approval_date' => 'date',
    ];

    public function distributionCenter()
    {
        return $this->belongsTo(DistributionCenter::class, 'distribution_center_id', 'id', 'distributionCenter');
    }
}
