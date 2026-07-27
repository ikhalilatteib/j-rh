<?php

declare(strict_types=1);

namespace Ikay\JRh\Tests;

use Ikay\JRh\JRhServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            MediaLibraryServiceProvider::class,
            JRhServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->createHostApplicationTables();
        $this->createMediaTable();

        $this->artisan('migrate')->run();
    }

    /**
     * The package ships neither a users table nor the salaries/advances tables: it expects
     * the host application to own all three. The employees migration adds a nullable FK to
     * users, and migrations 000002/000003 re-key existing salaries/advances onto employees.
     *
     * This is the "already employee-keyed" host shape, so those two data migrations should
     * detect there is nothing to convert and no-op.
     */
    protected function createHostApplicationTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('salaries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('employee_id')->index();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('prime', 12, 2)->default(0);
            $table->decimal('advance_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('advances', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('employee_id')->index();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('date');
            $table->text('reason')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * spatie/laravel-medialibrary publishes (rather than auto-loads) its migration, so the
     * media table has to be created by hand from the shipped stub.
     */
    protected function createMediaTable(): void
    {
        $stub = __DIR__.'/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub';

        if (! file_exists($stub)) {
            return;
        }

        (require $stub)->up();
    }

    /**
     * Note: interface_exists() on the Filament contracts is NOT a usable probe, because the
     * compat shims declare those very interfaces when Filament is absent. Panel only ever
     * comes from the real filament/filament package.
     */
    protected function isFilamentInstalled(): bool
    {
        return class_exists(\Filament\Panel::class);
    }
}
