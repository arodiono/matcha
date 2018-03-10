<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;

/**
 * Class UsernameAvailable
 * @package App\Validation\Rules
 */
class UsernameAvailable extends AbstractRule
{
    /**
     * @param string $input
     * @return bool
     */
    public function validate($input)
    {
        return User::where('username', $input)->count() === 0;
    }
}