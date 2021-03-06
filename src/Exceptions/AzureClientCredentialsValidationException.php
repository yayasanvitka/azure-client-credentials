<?php

namespace Yayasanvitka\AzureClientCredentials\Exceptions;

use JetBrains\PhpStorm\Pure;

/**
 * Class AzureClientCredentialsValidationException.
 *
 * @package Yayasanvitka\AzureClientCredentials\Exceptions
 */
class AzureClientCredentialsValidationException extends \Exception
{
    /**
     * Thrown when empty Tenant ID detected.
     *
     * @return static
     */
    #[Pure]
    public static function tenantIdIsEmpty(): self
    {
        return new static('Tenant ID is Empty');
    }

    /**
     * Thrown when empty Client ID detected.
     *
     * @return static
     */
    #[Pure]
    public static function clientIdIsEmpty(): self
    {
        return new static('Client ID is Empty');
    }

    /**
     * Thrown when empty Client Secret detected.
     *
     * @return static
     */
    #[Pure]
    public static function clientSecretIsEmpty(): self
    {
        return new static('Client Secret is Empty');
    }

    /**
     * Thrown when empty Cache Name detected.
     *
     * @return static
     */
    #[Pure]
    public static function cacheNameEmpty(): self
    {
        return new static('Cache Name is Empty');
    }

    /**
     * Thrown when empty Cache Tag detected.
     *
     * @return static
     */
    #[Pure]
    public static function cacheTagEmpty(): self
    {
        return new static('Cache Tag is Empty');
    }
}
