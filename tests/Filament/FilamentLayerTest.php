<?php

declare(strict_types=1);

namespace Ikay\JRh\Tests\Filament;

use Ikay\JRh\Models\Advance;
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Ikay\JRh\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Backward-compatibility net for consumers that DO have Filament (kitchen, jpro-eu).
 *
 * Skipped entirely when Filament is absent, which is exactly what the Filament-free CI job
 * does. The "with-filament" job installs filament/filament + barryvdh/laravel-dompdf as dev
 * dependencies and runs this file, so both halves of the split stay covered.
 */
final class FilamentLayerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->isFilamentInstalled()) {
            $this->markTestSkipped('filament/filament is not installed; the UI layer is an optional dependency.');
        }
    }

    #[Test]
    public function the_plugin_entry_point_consumers_register_still_exists(): void
    {
        $this->assertTrue(class_exists(\Ikay\JRh\JRhPlugin::class));
        $this->assertInstanceOf(\Filament\Contracts\Plugin::class, \Ikay\JRh\JRhPlugin::make());
        $this->assertSame('j-rh', \Ikay\JRh\JRhPlugin::make()->getId());
    }

    #[Test]
    public function the_resources_still_resolve_to_the_core_models(): void
    {
        $this->assertSame(Employee::class, \Ikay\JRh\Filament\Resources\EmployeeResource::getModel());
        $this->assertSame(Salary::class, \Ikay\JRh\Filament\Resources\SalaryResource::getModel());
        $this->assertSame(Advance::class, \Ikay\JRh\Filament\Resources\AdvanceResource::getModel());
    }

    #[Test]
    public function the_pdf_dependency_is_available_whenever_the_ui_layer_is(): void
    {
        $this->assertTrue(
            class_exists(\Barryvdh\DomPDF\Facade\Pdf::class),
            'barryvdh/laravel-dompdf is suggested alongside filament/filament and is needed by the salary bulletin.',
        );
    }
}
