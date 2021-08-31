<?php

namespace Yayasanvitka\AzureClientCredentials;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;

/**
 * Class AzureClientCredentialsServiceProvider.
 *
 * @package Yayasanvitka\AzureClientCredentials
 */
class AzureClientCredentialsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->offerPublishing();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/azure-client-credentials.php',
            'laravel-wablas'
        );
    }

    protected function offerPublishing()
    {
        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishes([
            __DIR__.'/../config/azure-client-credentials.php' => config_path('azure-client-credentials.php'),
        ], 'config');
    }
}
