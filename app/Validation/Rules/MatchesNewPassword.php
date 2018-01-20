<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Class MatchesNewPassword
 * @package App\Validation\Rules
 */
class MatchesNewPassword extends AbstractRule
{
    /**
     * @var string
     */
    protected $password;

    /**
     * MatchesNewPassword constructor.
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
        return $input === $this->password;
    }
}