<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: int
{
    use Concerns\DescribeEnum;

    case Draft = 1;
    case Unpaid = 2;
    case PendingReview = 3;
    case Paid = 4;
    case Rejected = 5;
}
