<?php

namespace Yayasanvitka\AzureClientCredentials\Test;

use Yayasanvitka\AzureClientCredentials\ClientCredentials;
use Yayasanvitka\AzureClientCredentials\Exceptions\AzureClientCredentialsException;
use Yayasanvitka\AzureClientCredentials\Test\Traits\HelperTrait;

/**
 * Class ClientCredentialsTest.
 *
 * @package Yayasanvitka\AzureClientCredentials\Test
 */
class ClientCredentialsTest extends TestCase
{
    use HelperTrait;

    /** @test */
    public function it_returns_formatted_url()
    {
        $this->setTenantUuidToRandomUuid();
        $this->setClientUuidToRandomUuid();
        $this->setClientSecretToRandomString();
        $this->setScopeToRandomUuid();

        $instance = app()->make(ClientCredentials::class);

        $url = config('azure-client-credentials.auth_url')
            .'/'
            .config('azure-client-credentials.tenant_id')
            .config('azure-client-credentials.auth_endpoint');

        $this->assertEquals($url, $instance->getUrl());
    }

    /**
     * Make sure your phpunit.xml is correct and populated!
     *
     * @test
     */
    public function it_throws_an_error_when_using_false_tenant_id()
    {
        $this->setTenantUuidToRandomUuid();

        $this->expectException(AzureClientCredentialsException::class);
        app()->make(ClientCredentials::class)->fetch();
    }

    /**
     * Make sure your phpunit.xml is correct and populated!
     *
     * @test
     */
    public function it_throws_an_error_when_using_false_client_id()
    {
        $this->setClientUuidToRandomUuid();

        $this->expectException(AzureClientCredentialsException::class);
        app()->make(ClientCredentials::class)->fetch();
    }

    /**
     * Make sure your phpunit.xml is correct and populated!
     *
     * @test
     */
    public function it_throws_an_error_when_using_false_client_secret()
    {
        $this->setClientSecretToRandomString();

        $this->expectException(AzureClientCredentialsException::class);
        app()->make(ClientCredentials::class)->fetch();
    }

    /**
     * Make sure your phpunit.xml is correct and populated!
     *
     * @test
     */
    public function it_throws_an_error_when_url_is_invalid()
    {
        config([
            'azure-client-credentials.auth_url' => 'https://google.com',
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        app()->make(ClientCredentials::class)->fetch();
    }

    /**
     * Make sure your phpunit.xml is correct and populated!
     *
     * @test
     */
    public function it_can_fetch_token_and_returns_valid_array()
    {
        $result = app()->make(ClientCredentials::class)->fetch();

        $this->assertArrayHasKey('token_type', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertArrayHasKey('ext_expires_in', $result);
        $this->assertArrayHasKey('access_token', $result);
    }

    /**
     * Make sure your phpunit.xml is correct and populated!
     *
     * @test
     */
    public function it_can_fetch_token_and_store_to_cache()
    {
        app()->make(ClientCredentials::class)->get();
        $this->assertNotNull(
            cache()->tags([config('azure-client-credentials.cache_tag')])->get(config('azure-client-credentials.client_id'))
        );
    }

    /**
     * Make sure your phpunit.xml is correct and populated!
     *
     * @test
     */
    public function it_can_fetch_token_from_cache()
    {
        $instance = app()->make(ClientCredentials::class);
        $oldResult = $instance->get();
        $cachedToken = cache()->tags([config('azure-client-credentials.cache_tag')])->get(config('azure-client-credentials.client_id'));

        $this->assertEquals($cachedToken, $instance->get());
        $this->assertEquals($oldResult, $instance->get());
    }

    /**
     * Make sure your phpunit.xml is correct and populated!
     *
     * @test
     */
    public function it_can_refresh_token_when_lifetime_is_over()
    {
        config([
            'azure-client-credentials.cache_lifetime' => 0,
        ]);

        $instance = app()->make(ClientCredentials::class);
        $oldResult = $instance->get();
        $cachedToken = cache()->tags([config('azure-client-credentials.cache_tag')])->get(config('azure-client-credentials.client_id'));
        $newResult = $instance->get();

        $this->assertNotEquals($cachedToken, $newResult);
        $this->assertNotEquals($oldResult, $newResult);
    }
}
