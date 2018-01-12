<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;

/**
 * Class EmailExist
 * @package App\Validation\Rules
 */
class EmailExist extends AbstractRule
{
    /**
     * @param string $input
     * @return bool
     */
    public function validate($input)
    {
        return User::where('email', $input)->count() === 1;
    }
}