<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class UsernameAvailableException
 * @package App\Validation\Exceptions
 */
class UsernameAvailableException extends ValidationException
{
    /**
     * @var array
     */
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Username is already exist.',
        ],
    ];
}