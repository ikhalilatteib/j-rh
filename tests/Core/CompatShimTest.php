<?php

declare(strict_types=1);

namespace Ikay\JRh\Tests\Core;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Ikay\JRh\Enums\AdvanceStatusEnum;
use Ikay\JRh\Enums\ContractTypeEnum;
use Ikay\JRh\Enums\EmployeeStatusEnum;
use Ikay\JRh\Enums\GenderEnum;
use Ikay\JRh\Enums\MaritalStatusEnum;
use Ikay\JRh\Enums\SalaryStatusEnum;
use Ikay\JRh\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

/**
 * The enums keep implementing Filament's contracts so that Filament consumers keep getting
 * translated labels, badge colours and icons. With Filament absent those interfaces are
 * supplied by compat/filament-contracts.php.
 *
 * Both CI jobs run this file: one proves the shims stand in correctly, the other proves the
 * real Filament interfaces are still satisfied.
 */
final class CompatShimTest extends TestCase
{
    /**
     * @return array<int, class-string>
     */
    public static function enums(): array
    {
        return [
            [AdvanceStatusEnum::class],
            [ContractTypeEnum::class],
            [EmployeeStatusEnum::class],
            [GenderEnum::class],
            [MaritalStatusEnum::class],
            [SalaryStatusEnum::class],
        ];
    }

    #[Test]
    public function every_enum_implements_the_filament_label_contract(): void
    {
        foreach (self::enums() as [$enum]) {
            $this->assertTrue(is_a($enum, HasLabel::class, allow_string: true), $enum.' must implement HasLabel.');
        }
    }

    #[Test]
    public function status_enums_implement_the_colour_and_icon_contracts(): void
    {
        foreach ([AdvanceStatusEnum::class, EmployeeStatusEnum::class, SalaryStatusEnum::class] as $enum) {
            $this->assertTrue(is_a($enum, HasColor::class, allow_string: true), $enum.' must implement HasColor.');
            $this->assertTrue(is_a($enum, HasIcon::class, allow_string: true), $enum.' must implement HasIcon.');
        }
    }

    #[Test]
    public function the_shims_are_only_used_when_filament_is_absent(): void
    {
        $declaringFile = (string) (new ReflectionClass(HasLabel::class))->getFileName();
        $shimPath = realpath(dirname(__DIR__, 2).'/compat/stubs/Filament/Support/Contracts/HasLabel.php');

        if ($this->isFilamentInstalled()) {
            $this->assertNotSame($shimPath, realpath($declaringFile), 'Filament is installed, so its real contract must win.');
            $this->assertStringContainsString('filament', $declaringFile);

            return;
        }

        $this->assertSame($shimPath, realpath($declaringFile), 'Without Filament the compat shim must supply the contract.');
    }
}
