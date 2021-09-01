<?php

namespace Yayasanvitka\AzureClientCredentials\Test;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Yayasanvitka\AzureClientCredentials\AzureClientCredentialsServiceProvider;

/**
 * Class TestCase.
 *
 * @package Yayasanvitka\AzureClientCredentials\Test
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            AzureClientCredentialsServiceProvider::class,
        ];
    }
}
