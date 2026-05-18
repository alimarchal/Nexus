<?php

namespace Alimarchal\IdGenerator\Tests;

use Alimarchal\IdGenerator\IdGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            IdGeneratorServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        // Load and run the package migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Create test tables
        $this->app['db']->connection()->getSchemaBuilder()->create('test_invoices', function ($table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('test_orders', function ($table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->timestamps();
        });
    }
}
