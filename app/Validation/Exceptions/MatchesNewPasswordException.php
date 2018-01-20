<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class MatchesNewPasswordException
 * @package App\Validation\Exceptions
 */
class MatchesNewPasswordException extends ValidationException
{
    /**
     * @var array
     */
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Password does not match.',
        ],
    ];
}