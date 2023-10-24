<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Database\Eloquent\Model;

class RemoteLocation extends Model
{
    protected $table = 'remote_locations';

    protected $fillable = [
        'customer_id', 'code', 'name', 'terminal_name', 'address',
        'latitude', 'longitude', 'online_at', 'sla',
        'pic_remote_name', 'pic_remote_phone_number',
        'pic_it_sat_name', 'pic_it_sat_phone_number',
        'distribution_center',

        'village_code', 'subdistrict_code', 'city_code', 'province_code', 'postal_code',

        'infrastructure_type', 'gsm_no', 'gsm_provider', 'gsm_no2', 'gsm_provider2', 'cid_no',
        'cid_fo_provider', 'cid_provider', 'fo_provider', 'pic_fo_provider_name', 'pic_fo_provider_phone_number',

        'pic_service_point_name', 'pic_service_point_phone_number', 'fo_contract_by_name', 'remark',
    ];

    protected $casts = [
        'online_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id', 'customer');
    }
}
