<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class MatchesOldPasswordException
 * @package App\Validation\Exceptions
 */
class MatchesOldPasswordException extends ValidationException
{
    /**
     * @var array
     */
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'The new password can not be identical to the old one.',
        ],
    ];
}