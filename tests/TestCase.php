<?php

namespace Yayasanvitka\AzureClientCredentials\Test;

use Illuminate\Foundation\Application;
use Yayasanvitka\AzureClientCredentials\AzureClientCredentialsServiceProvider;

/**
 * Class TestCase.
 *
 * @package Yayasanvitka\AzureClientCredentials\Test
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            AzureClientCredentialsServiceProvider::class,
        ];
    }
}
