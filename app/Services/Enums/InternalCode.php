<?php

declare(strict_types=1);

namespace App\Services\Enums;

enum InternalCode: int
{
    use DescribeEnum;

    case Nothing = 0;
    case MissingXMainAppToken = 101;
    case MissingXRcsAppToken = 102;
    case MissingXIssuanceAppToken = 103;
    case MissingXEmployerAppToken = 104;
    case MissingOrExpiredXMainTranToken = 130;
    case MissingOrExpiredXRcsTranToken = 131;
    case MissingXMainUsernameToken = 175;
    case MissingXSecqsUsernameToken = 176;

    public function description(): string
    {
        return match($this) {
            self::MissingXMainAppToken => __('Missing X-Main-App-Token'),
            self::MissingXRcsAppToken => __('Missing X-Rcs-App-Token'),
            self::MissingXIssuanceAppToken => __('Missing X-Issuance-App-Token'),
            self::MissingXEmployerAppToken => __('Missing X-Employer-App-Token'),
            self::MissingOrExpiredXMainTranToken => __('Missing or expired X-Main-Tran-Token'),
            self::MissingOrExpiredXRcsTranToken => __('Missing or expired X-Rcs-Tran-Token'),
            self::MissingXMainUsernameToken => __('Missing X-Main-Username-Token'),
            self::MissingXSecqsUsernameToken => __('Missing X-Secqs-Username-Token'),
            default => __('Nothing'),
        };
    }
}
