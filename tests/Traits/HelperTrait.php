<?php

namespace Yayasanvitka\AzureClientCredentials\Test\Traits;

use Illuminate\Support\Str;

/**
 * Trait HelperTrait.
 *
 * @package Yayasanvitka\AzureClientCredentials\Test\Traits
 */
trait HelperTrait
{
    private function setTenantUuidToNull()
    {
        config([
            'azure-client-credentials.tenant_id' => null,
        ]);
    }

    private function setTenantUuidToRandomString()
    {
        $string = Str::random('5');
        config([
            'azure-client-credentials.tenant_id' => $string,
        ]);
    }

    private function setTenantUuidToRandomUuid()
    {
        $string = Str::uuid()->toString();
        config([
            'azure-client-credentials.tenant_id' => $string,
        ]);
    }

    private function setClientUuidToNull()
    {
        config([
            'azure-client-credentials.client_id' => null,
        ]);
    }

    private function setClientUuidToRandomString()
    {
        $string = Str::random('5');
        config([
            'azure-client-credentials.client_id' => $string,
        ]);
    }

    private function setClientUuidToRandomUuid()
    {
        $string = Str::uuid()->toString();
        config([
            'azure-client-credentials.client_id' => $string,
        ]);
    }

    private function setClientSecretToNull()
    {
        config([
            'azure-client-credentials.client_secret' => null,
        ]);
    }

    private function setClientSecretToRandomString()
    {
        $string = Str::random('15');
        config([
            'azure-client-credentials.client_secret' => $string,
        ]);
    }

    private function setScopeToNull()
    {
        config([
            'azure-client-credentials.scope' => null,
        ]);
    }

    private function setScopeToRandomUuid()
    {
        $string = Str::uuid()->toString();
        config([
            'azure-client-credentials.scope' => $string,
        ]);
    }

    private function setScopeAsFormatted()
    {
        $string = Str::uuid()->toString();
        config([
            'azure-client-credentials.scope' => "api://{$string}/.default",
        ]);
    }

    private function setCacheTagToNull()
    {
        config([
            'azure-client-credentials.cache_tag' => null,
        ]);
    }
}
