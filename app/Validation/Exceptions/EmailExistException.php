<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Class EmailExistException
 * @package App\Validation\Exceptions
 */
class EmailExistException extends ValidationException
{
    /**
     * @var array
     */
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Can\'t find that email, sorry.',
        ],
    ];
}