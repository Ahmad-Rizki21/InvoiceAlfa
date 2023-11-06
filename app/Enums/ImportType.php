<?php

declare(strict_types=1);

namespace App\Enums;

enum ImportType: int
{
    use Concerns\DescribeEnum;

    case DistributionCenter = 1;
    case Franchise = 2;
    case Store = 3;
    case Invoice = 4;
}
