<?php

declare(strict_types=1);

namespace Ikay\JRh\Tests\Core;

use FilesystemIterator;
use Ikay\JRh\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Guards the core/UI boundary.
 *
 * Every shipped file except src/Filament/**, src/JRhPlugin.php, the compat shims and the
 * publishable test stubs must stay installable without filament/* and without
 * barryvdh/laravel-dompdf, since those are only suggested dependencies now. If someone
 * reintroduces a Filament reference into the models, enums, policies, traits, migrations or
 * factories, the package silently starts fataling for Filament-free consumers at class-load
 * time. This test fails first instead.
 *
 * Only shipped code is scanned; tests/ is dev-only and never installed into a consumer.
 */
final class FilamentBoundaryTest extends TestCase
{
    /**
     * Shipped directories, relative to the package root.
     *
     * @var array<int, string>
     */
    private const SHIPPED_DIRECTORIES = ['src', 'database', 'config', 'compat', 'stubs'];

    /**
     * Shipped paths allowed to reference the optional dependencies.
     *
     * @var array<int, string>
     */
    private const OPTIONAL_DEPENDENCY_SURFACE = [
        'src/Filament/',
        'src/JRhPlugin.php',
        'compat/',
        'stubs/tests/',
    ];

    /**
     * @var array<int, string>
     */
    private const OPTIONAL_DEPENDENCY_NAMESPACES = ['Filament\\', 'Barryvdh\\'];

    /**
     * The only Filament symbols core code may name. They are the three contracts the enums
     * implement, and compat/filament-contracts.php guarantees they exist even when Filament
     * is not installed. Anything else from Filament\ would fatal in a Filament-free app.
     *
     * @var array<int, string>
     */
    private const SHIMMED_CONTRACTS = [
        'Filament\\Support\\Contracts\\HasLabel',
        'Filament\\Support\\Contracts\\HasColor',
        'Filament\\Support\\Contracts\\HasIcon',
    ];

    #[Test]
    public function the_core_never_references_an_optional_dependency(): void
    {
        $offenders = [];

        foreach ($this->shippedPhpFiles() as $relativePath => $contents) {
            if ($this->isOptionalDependencySurface($relativePath)) {
                continue;
            }

            foreach ($this->optionalDependencySymbolsIn($contents) as $symbol) {
                $offenders[] = $relativePath.' references '.$symbol;
            }
        }

        $this->assertSame([], $offenders, implode("\n", array_merge([
            'Core files may only name the three shimmed Filament contracts.',
            'Move the code under src/Filament/, or make the dependency a hard require again.',
        ], $offenders)));
    }

    #[Test]
    public function the_filament_ui_layer_still_exists_behind_the_boundary(): void
    {
        $uiFiles = [];

        foreach ($this->shippedPhpFiles() as $relativePath => $contents) {
            if (! str_starts_with($relativePath, 'src/')) {
                continue;
            }

            if ($this->optionalDependencySymbolsIn($contents) !== []) {
                $uiFiles[] = $relativePath;
            }
        }

        $this->assertNotSame([], $uiFiles, 'Expected the Filament UI layer to still ship in this package.');
        $this->assertContains('src/JRhPlugin.php', $uiFiles, 'JRhPlugin must stay in this package for backward compatibility.');

        foreach ($uiFiles as $path) {
            $this->assertTrue(
                str_starts_with($path, 'src/Filament/') || $path === 'src/JRhPlugin.php',
                "{$path} depends on an optional package but lives outside the documented UI surface.",
            );
        }
    }

    #[Test]
    public function composer_json_does_not_hard_require_the_optional_dependencies(): void
    {
        /** @var array{require: array<string, string>, suggest?: array<string, string>} $composer */
        $composer = json_decode(
            (string) file_get_contents($this->packageRoot().'/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $require = array_keys($composer['require'] ?? []);

        $this->assertNotContains('filament/filament', $require);
        $this->assertNotContains('filament/support', $require);
        $this->assertNotContains('barryvdh/laravel-dompdf', $require);

        $this->assertArrayHasKey('filament/filament', $composer['suggest'] ?? []);
        $this->assertArrayHasKey('barryvdh/laravel-dompdf', $composer['suggest'] ?? []);
    }

    /**
     * Fully-qualified references to an optional dependency, minus the three shimmed
     * contracts that core code is allowed to name.
     *
     * @return array<int, string>
     */
    private function optionalDependencySymbolsIn(string $contents): array
    {
        $found = [];

        foreach (self::OPTIONAL_DEPENDENCY_NAMESPACES as $namespace) {
            preg_match_all('/\\\\?'.preg_quote($namespace, '/').'[A-Za-z0-9_\\\\]+/', $contents, $matches);

            foreach ($matches[0] as $symbol) {
                $symbol = ltrim($symbol, '\\');

                if (in_array($symbol, self::SHIMMED_CONTRACTS, strict: true)) {
                    continue;
                }

                $found[$symbol] = $symbol;
            }
        }

        return array_values($found);
    }

    /**
     * @return array<string, string>
     */
    private function shippedPhpFiles(): array
    {
        $root = $this->packageRoot();
        $files = [];

        foreach (self::SHIPPED_DIRECTORIES as $directory) {
            $path = $root.'/'.$directory;

            if (! is_dir($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = str_replace($root.DIRECTORY_SEPARATOR, '', (string) $file->getRealPath());
                $files[$relativePath] = (string) file_get_contents((string) $file->getRealPath());
            }
        }

        ksort($files);

        return $files;
    }

    private function isOptionalDependencySurface(string $relativePath): bool
    {
        foreach (self::OPTIONAL_DEPENDENCY_SURFACE as $prefix) {
            if (str_starts_with($relativePath, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function packageRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}
