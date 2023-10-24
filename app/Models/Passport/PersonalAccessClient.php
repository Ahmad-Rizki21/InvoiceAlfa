<?php

namespace App\Models\Passport;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Laravel\Passport\PersonalAccessClient as BasePersonalAccesClient;

class PersonalAccessClient extends BasePersonalAccesClient
{
    use HasUlids;
}
