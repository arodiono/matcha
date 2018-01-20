<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Class MatchesOldPassword
 * @package App\Validation\Rules
 */
class MatchesOldPassword extends AbstractRule
{
    /**
     * @var string
     */
    protected $password;

    /**
     * MatchesOldPassword constructor.
     * @param string $password
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * @param string $input
     * @return bool
     */
    public function validate($input)
    {
        return !password_verify($input, $this->password);
    }
}