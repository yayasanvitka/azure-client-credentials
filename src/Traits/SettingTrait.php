<?php

namespace Yayasanvitka\AzureClientCredentials\Traits;

use Illuminate\Support\Str;
use Yayasanvitka\AzureClientCredentials\ClientCredentials;
use Yayasanvitka\AzureClientCredentials\Exceptions\AzureClientCredentialsValidationException;
use Yayasanvitka\AzureClientCredentials\Exceptions\InvalidGUIDSupplied;

/**
 * Trait SettingTrait.
 *
 * @package Yayasanvitka\AzureClientCredentials\Traits
 */
trait SettingTrait
{
    public string $tenantId;
    public string $clientId;
    public string $clientSecret;
    public string $scope;

    /**
     * @param string $tenantId
     *
     * @throws \Throwable
     *
     * @return ClientCredentials|self
     */
    public function setTenantId(string $tenantId): self
    {
        throw_if(blank($tenantId), AzureClientCredentialsValidationException::tenantIdIsEmpty());
        $this->tenantId = $this->validateUuid($tenantId, 'tenant');

        return $this;
    }

    /**
     * @param string $clientId
     *
     * @throws \Throwable
     *
     * @return ClientCredentials|self
     */
    public function setClientId(string $clientId): self
    {
        throw_if(blank($clientId), AzureClientCredentialsValidationException::clientIdIsEmpty());
        $this->clientId = $this->validateUuid($clientId, 'application');

        return $this;
    }

    /**
     * @param $clientSecret
     *
     * @throws \Throwable
     *
     * @return ClientCredentials|self
     */
    public function setClientSecret($clientSecret): self
    {
        throw_if(blank($clientSecret), AzureClientCredentialsValidationException::clientSecretIsEmpty());
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Format the scope.
     * If the scope provided starts with 'api://' and ends with '/.default', return as is.
     * If not, append with 'api://' and prepend with '/.default' as the requirements on Microsoft Azure OAuth2.
     *
     * @param $scope
     *
     * @return ClientCredentials|self
     */
    public function setScope($scope): self
    {
        if (blank($scope)) {
            $scope = 'https://graph.microsoft.com/.default';
        }

        if ($scope == 'https://graph.microsoft.com/.default') {
            $this->scope = $scope;

            return $this;
        }

        if (Str::startsWith($scope, 'api://') && Str::endsWith($scope, '/.default')) {
            $this->scope = $scope;

            return $this;
        }

        $this->scope = "api://{$scope}/.default";

        return $this;
    }

    /**
     * Get TenantId.
     *
     * @return string
     */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * Get ClientId.
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Get ClientSecret.
     *
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Get Scope.
     *
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * Get Endpoint URL, formatted with the TenantId supplied.
     */
    public function getUrl(): string
    {
        return config('azure-client-credentials.auth_url')
            .'/'
            .$this->getTenantId()
            .config('azure-client-credentials.auth_endpoint');
    }

    /**
     * @param string $uuid
     * @param string $type
     *
     * @throws \Throwable
     *
     * @return string
     */
    private function validateUuid(string $uuid, string $type): string
    {
        $type = match ($type) {
            'application' => 'Application (client) ID (GUID)',
            'tenant' => 'Directory (tenant) ID (GUID)',
        };

        throw_unless(Str::isUuid($uuid), new InvalidGUIDSupplied("Invalid {$type} supplied!"));

        return $uuid;
    }
}
