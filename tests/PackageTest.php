<?php

namespace Yayasanvitka\AzureClientCredentials\Test;

use Yayasanvitka\AzureClientCredentials\ClientCredentials;
use Yayasanvitka\AzureClientCredentials\Exceptions\AzureClientCredentialsValidationException;
use Yayasanvitka\AzureClientCredentials\Exceptions\InvalidGUIDSupplied;
use Yayasanvitka\AzureClientCredentials\Test\Traits\HelperTrait;

/**
 * Class PackageTest.
 *
 * @package Yayasanvitka\AzureClientCredentials\Test
 */
class PackageTest extends TestCase
{
    use HelperTrait;

    /** @test */
    public function it_loads_configurations()
    {
        $configs = config('azure-client-credentials');

        $this->assertArrayHasKey('cache_tag', $configs);
        $this->assertArrayHasKey('cache_lifetime', $configs);
        $this->assertArrayHasKey('auth_url', $configs);
        $this->assertArrayHasKey('auth_endpoint', $configs);
        $this->assertArrayHasKey('tenant_id', $configs);
        $this->assertArrayHasKey('client_id', $configs);
        $this->assertArrayHasKey('client_secret', $configs);
        $this->assertArrayHasKey('scope', $configs);
    }

    /** @test */
    public function it_throws_error_when_tenant_id_is_empty()
    {
        $this->setTenantUuidToNull();

        $this->expectException(\TypeError::class);

        app()->make(ClientCredentials::class);
    }

    /** @test */
    public function it_throws_error_when_tenant_id_is_invalid()
    {
        $this->setTenantUuidToRandomString();

        $this->expectException(InvalidGUIDSupplied::class);
        $this->expectExceptionMessage('Invalid Directory (tenant) ID (GUID) supplied!');

        app()->make(ClientCredentials::class);
    }

    /** @test */
    public function it_throws_error_when_client_id_is_empty()
    {
        $this->setTenantUuidToRandomUuid();
        $this->setClientUuidToNull();

        $this->expectException(\TypeError::class);

        app()->make(ClientCredentials::class);
    }

    /** @test */
    public function it_throws_error_when_client_id_is_invalid()
    {
        $this->setTenantUuidToRandomUuid();
        $this->setClientUuidToRandomString();

        $this->expectException(InvalidGUIDSupplied::class);
        $this->expectExceptionMessage('Invalid Application (client) ID (GUID) supplied!');

        app()->make(ClientCredentials::class);
    }

    /** @test */
    public function it_throws_error_when_blank_client_secret_provided()
    {
        $this->setTenantUuidToRandomUuid();
        $this->setClientUuidToRandomUuid();
        $this->setClientSecretToNull();

        $this->expectException(AzureClientCredentialsValidationException::class);
        $this->expectExceptionMessage('Client Secret is Empty');

        app()->make(ClientCredentials::class);
    }

    /** @test */
    public function it_returns_default_value_when_blank_scope_provided()
    {
        $this->setTenantUuidToRandomUuid();
        $this->setClientUuidToRandomUuid();
        $this->setClientSecretToRandomString();
        $this->setScopeToNull();

        $instance = app()->make(ClientCredentials::class);
        $this->assertEquals('https://graph.microsoft.com/.default', $instance->getScope());
    }

    /** @test */
    public function it_returns_formatted_scope()
    {
        $this->setTenantUuidToRandomUuid();
        $this->setClientUuidToRandomUuid();
        $this->setClientSecretToRandomString();
        $this->setScopeToRandomUuid();

        $scope = 'api://'.config('azure-client-credentials.scope').'/.default';

        $instance = app()->make(ClientCredentials::class);
        $this->assertEquals($scope, $instance->getScope());
    }

    /** @test */
    public function it_does_not_format_formatted_scope()
    {
        $this->setTenantUuidToRandomUuid();
        $this->setClientUuidToRandomUuid();
        $this->setClientSecretToRandomString();
        $this->setScopeAsFormatted();

        $instance = app()->make(ClientCredentials::class);
        $this->assertEquals(config('azure-client-credentials.scope'), $instance->getScope());
    }
}
