<?php

namespace Cartino\Tests;

use Cartino\CartinoServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            CartinoServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cartino.currencies', [
            'EUR' => [
                'name' => 'Euro',
                'code' => 'EUR',
                'symbol' => '€',
                'format' => '1.234,56 €',
                'exchange_rate' => 1,
            ],
        ]);

        $app['config']->set('cartino.system_locales', ['en', 'it']);
    }
}
