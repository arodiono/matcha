<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;

/**
 * Class MatchesPassword
 * @package App\Validation\Rules
 */
class MatchesPassword extends AbstractRule
{
    /**
     * @var string
     */
    protected $password;

    /**
     * MatchesPassword constructor.
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
        return password_verify($input, $this->password);
    }
}