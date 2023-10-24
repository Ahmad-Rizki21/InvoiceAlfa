<?php

declare(strict_types=1);

namespace App\Services\SssBridge\Enums;

use App\Services\Enums\DescribeEnum;

enum Api: string
{
    use DescribeEnum;

    case Bank = 'bank';
    case Contribution = 'contribution';
    case Disclosure = 'disclosure';
    case Eligibility = 'eligibility';
    case Employer = 'employer';
    case Loan = 'loan';
    case Main = 'main';
    case Maternity = 'maternity';
    case Prn = 'prn';
    case Rcs = 'rcs';
    case RcsEmployer = 'rcs_employer';
    case RcsMember = 'rcs_member';
    case Secqs = 'secqs';
    case Issuance = 'issuance';
}
