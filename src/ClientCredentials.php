<?php

namespace Yayasanvitka\AzureClientCredentials;

use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Yayasanvitka\AzureClientCredentials\Exceptions\AzureClientCredentialsException;
use Yayasanvitka\AzureClientCredentials\Traits\SettingTrait;

/**
 * Class ClientCredentials.
 *
 * @package Yayasanvitka\AzureClientCredentials
 */
class ClientCredentials
{
    use SettingTrait;

    protected TokenCache $tokenCache;

    /**
     * Client Credentials Constructor.
     *
     * @param string|null $tenantId
     * @param string|null $clientId
     * @param string|null $clientSecret
     * @param string|null $scope
     *
     * @throws \Throwable
     */
    public function __construct(
        string $tenantId = null,
        string $clientId = null,
        string $clientSecret = null,
        string $scope = null
    ) {
        if (blank($tenantId)) {
            $tenantId = config('azure-client-credentials.tenant_id');
        }

        if (blank($clientId)) {
            $clientId = config('azure-client-credentials.client_id');
        }

        if (blank($clientSecret)) {
            $clientSecret = config('azure-client-credentials.client_secret');
        }

        if (blank($scope)) {
            $scope = config('azure-client-credentials.scope');
        }

        $this->setTenantId($tenantId);
        $this->setClientId($clientId);
        $this->setClientSecret($clientSecret);
        $this->setScope($scope);

        $this->tokenCache = new TokenCache(cacheName: $this->getClientId() . '_' . $this->getScope());
    }

    /**
     * Get the Access Token.
     * If token is not exists on cache, fetch a new one.
     *
     * @throws \Illuminate\Http\Client\RequestException
     *
     * @return string
     */
    public function get(): string
    {
        $accessToken = $this->tokenCache->getCache();

        if (blank($accessToken)) {
            $response = $this->fetch();
            $accessToken = $response['access_token'];
            $this->tokenCache->setCache(token: $accessToken);
        }

        return $accessToken;
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     *
     * @return array|mixed
     */
    #[ArrayShape([
        'token_type' => 'string',
        'expires_in' => 'int',
        'ext_expires_in' => 'int',
        'access_token' => 'string',
    ])]
    public function fetch()
    {
        return Http::asForm()
            ->post($this->getUrl(), [
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'scope' => $this->getScope(),
                'grant_type' => 'client_credentials',
            ])->throw(function ($response, $e) {
                $jsonError = $response->json();
                if (!blank($jsonError)) {
                    throw new AzureClientCredentialsException(
                        "[{$jsonError['error']}] {$jsonError['error_description']}", $jsonError['error_codes'][0], $e
                    );
                }
            })->json();
    }

}
