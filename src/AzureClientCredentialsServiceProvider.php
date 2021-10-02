<?php

namespace Yayasanvitka\AzureClientCredentials;

use Illuminate\Support\ServiceProvider;

/**
 * Class AzureClientCredentialsServiceProvider.
 *
 * @package Yayasanvitka\AzureClientCredentials
 */
class AzureClientCredentialsServiceProvider extends ServiceProvider
{
    /**
     * Boot the application.
     */
    public function boot()
    {
        $this->offerPublishing();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/azure-client-credentials.php',
            'azure-client-credentials'
        );
    }

    /**
     * Only publish if `config_path` function is available.
     * It would not offer publishing in Lumen.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../config/azure-client-credentials.php' => config_path('azure-client-credentials.php'),
            ], 'config');
        }
    }
}
