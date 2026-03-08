<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! isset($_SERVER['LAMBDA_TASK_ROOT'])) {
            return;
        }

        $manifestPath = public_path('build/manifest.json');
        if (! is_file($manifestPath)) {
            return;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($manifest)) {
            return;
        }

        $assets = ['build/manifest.json'];
        foreach ($manifest as $chunk) {
            if (! is_array($chunk)) {
                continue;
            }

            if (! empty($chunk['file']) && is_string($chunk['file'])) {
                $assets[] = 'build/' . ltrim($chunk['file'], '/');
            }

            foreach ($chunk['css'] ?? [] as $cssFile) {
                if (is_string($cssFile) && $cssFile !== '') {
                    $assets[] = 'build/' . ltrim($cssFile, '/');
                }
            }

            foreach ($chunk['assets'] ?? [] as $assetFile) {
                if (is_string($assetFile) && $assetFile !== '') {
                    $assets[] = 'build/' . ltrim($assetFile, '/');
                }
            }
        }

        Config::set('bref.assets', array_values(array_unique(array_merge(
            Config::get('bref.assets', []),
            $assets
        ))));
    }
}
