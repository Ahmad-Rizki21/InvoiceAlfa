<?php

declare(strict_types=1);

namespace App\Services\Auth\Enums;

use App\Services\Enums\DescribeEnum;

enum UserType: int
{
    use DescribeEnum;

    case Member = 1;
    case Employer = 2;
    case CoverageCollectionPartner = 3;
    case SmallBusinessWageSubsidy = 4;
    case Administrator = 99;
}
