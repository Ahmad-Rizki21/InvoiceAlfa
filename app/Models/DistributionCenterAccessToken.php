<?php

namespace App\Models;

class DistributionCenterAccessToken extends UserAccessToken
{
    protected $table = 'distribution_center_access_tokens';

    public function user()
    {
        return $this->belongsTo(DistributionCenter::class, 'user_id', 'id', 'user');
    }

    public static function tokenPrefix()
    {
        return 'd';
    }
}
