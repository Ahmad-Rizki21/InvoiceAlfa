<?php

declare(strict_types=1);

namespace App\Enums;

enum TransferToType: int
{
    use Concerns\DescribeEnum;

    case BankTransfer = 1;
    case VirtualAccount = 2;
}
