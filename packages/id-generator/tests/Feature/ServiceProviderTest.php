<?php

use Alimarchal\IdGenerator\IdGenerator;
use Alimarchal\IdGenerator\IdGeneratorServiceProvider;

describe('Service Provider Registration', function () {
    it('registers the service provider', function () {
        $providers = $this->app->getLoadedProviders();

        expect($providers)->toHaveKey(IdGeneratorServiceProvider::class);
    });

    it('binds IdGenerator as singleton', function () {
        $instance1 = $this->app->make(IdGenerator::class);
        $instance2 = $this->app->make(IdGenerator::class);

        expect($instance1)->toBe($instance2);
    });

    it('can resolve IdGenerator from container', function () {
        $generator = $this->app->make(IdGenerator::class);

        expect($generator)->toBeInstanceOf(IdGenerator::class);
    });
});

describe('Helper Functions Loading', function () {
    it('loads helper functions automatically', function () {
        expect(function_exists('generateUniqueId'))->toBeTrue();
        expect(function_exists('generateUniqueIdWithPrefix'))->toBeTrue();
    });

    it('helper functions work with resolved instance', function () {
        $helperResult = generateUniqueId('invoice', 'test_invoices', 'invoice_no');

        $generator = $this->app->make(IdGenerator::class);
        $classResult = $generator->generate('invoice', 'test_invoices', 'invoice_no');

        expect($helperResult)->toStartWith('INV-');
        expect($classResult)->toStartWith('INV-');
    });
});

describe('Laravel Integration', function () {
    it('works with dependency injection in controllers', function () {
        // Simulate controller method with dependency injection
        $controller = new class
        {
            public function store(IdGenerator $generator)
            {
                return $generator->generate('invoice', 'test_invoices', 'invoice_no');
            }
        };

        $result = $this->app->call([$controller, 'store']);

        expect($result)->toStartWith('INV-');
    });

    it('maintains singleton across different resolutions', function () {
        $directResolve = app(IdGenerator::class);
        $makeResolve = $this->app->make(IdGenerator::class);

        expect($directResolve)->toBe($makeResolve);
    });
});
