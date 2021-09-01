<?php

namespace Yayasanvitka\AzureClientCredentials\Test;

use Illuminate\Support\Str;
use Yayasanvitka\AzureClientCredentials\Exceptions\AzureClientCredentialsValidationException;
use Yayasanvitka\AzureClientCredentials\Test\Traits\HelperTrait;
use Yayasanvitka\AzureClientCredentials\TokenCache;

/**
 * Class CacheTest.
 *
 * @package Yayasanvitka\AzureClientCredentials\Test
 */
class CacheTest extends TestCase
{
    use HelperTrait;

    /** @test */
    public function throws_error_when_cache_name_is_not_provided()
    {
        $this->expectException(\TypeError::class);
        app()->make(TokenCache::class, [
            'cacheName' => null,
        ]);
    }

    /** @test */
    public function throws_error_when_cache_name_is_empty()
    {
        $this->expectException(AzureClientCredentialsValidationException::class);
        $this->expectExceptionMessage('Cache Name is Empty');
        app()->make(TokenCache::class, [
            'cacheName' => '',
        ]);
    }

    /** @test */
    public function throws_error_when_cache_tag_is_empty()
    {
        $this->setCacheTagToNull();

        $this->expectException(AzureClientCredentialsValidationException::class);
        $this->expectExceptionMessage('Cache Tag is Empty');
        app()->make(TokenCache::class, [
            'cacheName' => 'test',
        ]);
    }

    /** @test */
    public function it_created_with_valid_settings()
    {
        $cacheName = Str::random();
        $tokenCache = app()->make(TokenCache::class, [
            'cacheName' => $cacheName,
        ]);

        $this->assertEquals(config('azure-client-credentials.cache_tag'), $tokenCache->getCacheTag());
        $this->assertEquals(config('azure-client-credentials.cache_lifetime'), $tokenCache->getLifetime());
        $this->assertEquals($cacheName, $tokenCache->getCacheName());
    }

    /** @test */
    public function it_returns_empty_string_when_cache_is_empty()
    {
        $cacheName = Str::random();
        $tokenCache = app()->make(TokenCache::class, [
            'cacheName' => $cacheName,
        ])->getCache();

        $this->assertEquals('', $tokenCache);
    }

    /** @test */
    public function it_can_store_and_read_cache()
    {
        $cacheName = Str::random();
        $cachedValue = Str::uuid()->toString();

        $tokenCache = app()->make(TokenCache::class, [
            'cacheName' => $cacheName,
        ]);

        $tokenCache->setCache($cachedValue);
        $this->assertEquals($cachedValue, $tokenCache->getCache());
    }

    /** @test */
    public function it_removes_cache_when_lifetime_is_over()
    {
        config([
            'azure-client-credentials.cache_lifetime' => 0,
        ]);

        $cacheName = Str::random();
        $cachedValue = Str::uuid()->toString();

        $tokenCache = app()->make(TokenCache::class, [
            'cacheName' => $cacheName,
        ]);

        $tokenCache->setCache($cachedValue);
        $this->assertEquals('', $tokenCache->getCache());
    }

}
