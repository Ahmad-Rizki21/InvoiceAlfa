<?php

declare(strict_types=1);

namespace App\Enums;

enum SettingKey: int
{
    use Concerns\DescribeEnum;

    case StampDuty = 1;
    case PpnPercentage = 2;
    case InvoiceNote = 3;
    case SignatoryName = 4;
    case SignatoryPosition = 5;
    case BankTransferName = 6;
    case BankTransferAccountNumber = 7;
    case BankTransferAccountName = 8;
    case SignatureImage = 9;
    case StampImage = 10;
    case InjectInvoiceNo = 11;

}
