<?php

declare(strict_types=1);

namespace App\Contractors\Tests\Unit;

use App\Contractors\Domain\Entity\Company;
use PHPUnit\Framework\TestCase;

final class CompanyNormalizationTest extends TestCase
{
    public function testNormalizesCompanyNameForFutureEntityResolution(): void
    {
        self::assertSame('abc electrical', Company::normalizeCompanyName('  ABC Electrical, LLC  '));
        self::assertSame('ironclad steel', Company::normalizeCompanyName('Ironclad Steel Corp.'));
        self::assertSame('clearflow plumbing', Company::normalizeCompanyName('ClearFlow Plumbing Inc.'));
        self::assertSame('peak mechanical', Company::normalizeCompanyName('Peak   Mechanical   Co.'));
    }
}
