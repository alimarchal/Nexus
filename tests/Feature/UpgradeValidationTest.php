<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpgradeValidationTest extends TestCase
{
    /**
     * Test that Laravel framework is at the expected version.
     */
    public function test_laravel_version_is_correct(): void
    {
        $version = app()->version();
        
        // Verify Laravel 12.x is installed
        $this->assertStringStartsWith('12.', $version);
        
        // Verify it's at least 12.42.0
        $this->assertTrue(version_compare($version, '12.42.0', '>='));
    }

    /**
     * Test that Vite asset compilation works correctly.
     */
    public function test_vite_manifest_exists_after_build(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        // Check if build manifest exists (should be created by npm run build)
        $this->assertFileExists($manifestPath, 'Vite manifest should exist after build');
        
        // Verify manifest is valid JSON
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest, 'Vite manifest should be valid JSON');
        
        // Verify expected assets are present
        $this->assertArrayHasKey('resources/css/app.css', $manifest);
        $this->assertArrayHasKey('resources/js/app.js', $manifest);
    }

    /**
     * Test that Tailwind CSS classes are compiled properly.
     */
    public function test_tailwind_css_is_compiled(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Build manifest not found. Run npm run build first.');
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        
        $this->assertNotNull($cssFile, 'CSS file should be in manifest');
        
        $cssPath = public_path('build/' . $cssFile);
        $this->assertFileExists($cssPath, 'Compiled CSS file should exist');
        
        // Read CSS content
        $cssContent = file_get_contents($cssPath);
        
        // Verify Tailwind utility classes are present (e.g., common ones)
        $this->assertStringContainsString('--tw-', $cssContent, 'Tailwind CSS variables should be present');
        
        // Verify custom classes are present
        $this->assertStringContainsString('.bg-bank-green', $cssContent, 'Custom CSS classes should be present');
    }

    /**
     * Test that required PHP extensions are available.
     */
    public function test_required_php_extensions_are_loaded(): void
    {
        $requiredExtensions = [
            'bcmath',
            'ctype',
            'curl',
            'dom',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pcre',
            'pdo',
            'tokenizer',
            'xml',
        ];

        foreach ($requiredExtensions as $extension) {
            $this->assertTrue(
                extension_loaded($extension),
                "Required PHP extension '{$extension}' is not loaded"
            );
        }
    }

    /**
     * Test that critical Laravel services boot correctly.
     */
    public function test_critical_services_boot_correctly(): void
    {
        // Test database connection
        $this->assertNotNull(app('db'));
        
        // Test config system
        $this->assertNotNull(config('app.name'));
        
        // Test cache system
        $this->assertNotNull(app('cache'));
        
        // Test view system
        $this->assertTrue(view()->exists('welcome'));
    }

    /**
     * Test that Jetstream is properly configured.
     */
    public function test_jetstream_is_configured(): void
    {
        // Verify Jetstream service provider is registered
        $providers = array_keys(app()->getLoadedProviders());
        
        $this->assertContains(
            'Laravel\Jetstream\JetstreamServiceProvider',
            $providers,
            'Jetstream service provider should be registered'
        );
    }

    /**
     * Test that Livewire is properly configured.
     */
    public function test_livewire_is_configured(): void
    {
        // Verify Livewire service provider is registered
        $providers = array_keys(app()->getLoadedProviders());
        
        $this->assertContains(
            'Livewire\LivewireServiceProvider',
            $providers,
            'Livewire service provider should be registered'
        );
    }

    /**
     * Test that Spatie packages are properly configured.
     */
    public function test_spatie_packages_are_configured(): void
    {
        // Verify Spatie permission package is loaded
        $providers = array_keys(app()->getLoadedProviders());
        
        $this->assertContains(
            'Spatie\Permission\PermissionServiceProvider',
            $providers,
            'Spatie Permission service provider should be registered'
        );
        
        $this->assertContains(
            'Spatie\Activitylog\ActivitylogServiceProvider',
            $providers,
            'Spatie Activity Log service provider should be registered'
        );
    }

    /**
     * Test that asset compilation produces optimized output.
     */
    public function test_assets_are_optimized_for_production(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Build manifest not found. Run npm run build first.');
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        // Verify files have hash for cache busting
        foreach ($manifest as $entry) {
            $file = $entry['file'] ?? null;
            $this->assertNotNull($file);
            $this->assertMatchesRegularExpression(
                '/\-[a-zA-Z0-9]+\.(js|css)$/',
                $file,
                'Asset files should have hash for cache busting'
            );
        }
    }
}
