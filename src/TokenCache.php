<?php

namespace Yayasanvitka\AzureClientCredentials;

use Yayasanvitka\AzureClientCredentials\Exceptions\AzureClientCredentialsValidationException;

/**
 * Class TokenCache.
 *
 * @package Yayasanvitka\AzureClientCredentials
 */
class TokenCache
{
    protected int $lifetime;

    /**
     * @param string $cacheName
     * @param string $cacheTag
     *
     * @throws \Throwable
     */
    public function __construct(
        protected string $cacheName = '',
        protected string $cacheTag = '',
    ) {
        if (blank($this->cacheName)) {
            $this->setCacheName(config('azure-client-credentials.cache_tag'));
        }

        $this->lifetime = config('azure-client-credentials.cache_lifetime');
    }

    /**
     * Set cache value.
     *
     * @param string $token
     *
     * @throws \Exception
     */
    public function setCache(string $token)
    {
        cache()
            ->tags([$this->getCacheTag()])
            ->put($this->getCacheName(), $token, $this->lifetime);
    }

    /**
     * Get cache value.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getCache(): string
    {
        if (cache()->tags([$this->getCacheTag()])->has($this->getCacheName())) {
            return cache()->tags([$this->getCacheTag()])->get($this->getCacheName());
        }

        return '';
    }

    /**
     * Get the cache tag used to store the JWT token.
     *
     * @return string
     */
    public function getCacheTag(): string
    {
        return $this->cacheTag;
    }

    /**
     * Get the cache name used to store the JWT token.
     *
     * @return string
     */
    public function getCacheName(): string
    {
        return $this->cacheName;
    }

    /**
     * Set the cache tag.
     *
     * @param string $tag
     *
     * @throws \Throwable
     */
    public function setCacheTag(string $tag)
    {
        throw_if(blank($tag), AzureClientCredentialsValidationException::cacheTagEmpty());

        $this->cacheTag = $tag;
    }

    /**
     * Set the cache name.
     *
     * @param string $name
     *
     * @throws \Throwable
     */
    public function setCacheName(string $name)
    {
        throw_if(blank($name), AzureClientCredentialsValidationException::cacheNameEmpty());

        $this->cacheName = $name;
    }
}
