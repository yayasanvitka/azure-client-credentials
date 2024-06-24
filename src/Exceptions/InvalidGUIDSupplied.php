<?php

namespace Yayasanvitka\AzureClientCredentials\Exceptions;

use JetBrains\PhpStorm\Pure;

/**
 * Class InvalidGUIDSupplied.
 *
 * @package Yayasanvitka\AzureClientCredentials\Exceptions
 */
class InvalidGUIDSupplied extends \Exception
{
    /**
     * @param string $message
     * @param \Throwable|null $previous
     */
    #[Pure]
    public function __construct($message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}
