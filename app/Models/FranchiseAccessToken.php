<?php

namespace App\Models;

class FranchiseAccessToken extends UserAccessToken
{
    protected $table = 'franchise_access_tokens';

    public function user()
    {
        return $this->belongsTo(Franchise::class, 'user_id', 'id', 'user');
    }

    public static function tokenPrefix()
    {
        return 'f';
    }
}
