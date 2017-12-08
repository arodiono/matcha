<?php

namespace App\Auth;

use App\Models\User;

/**
 * Class Auth
 * @package App\Auth
 */
class Auth
{
    /**
     * @return User
     */
    public function user() : ?User
    {
        return User::find($_SESSION['user']);
    }

    /**
     * @return bool
     */
    public function check()
    {
        return isset($_SESSION['user']);
    }

    /**
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function attempt($email, $password)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return false;
        }

        if (password_verify($password, $user->password)) {
            $_SESSION['user'] = $user->id;
            return true;
        }
        return false;
    }

    /**
     * Remove user from session
     */
    public function logout()
    {
        unset($_SESSION['user']);
    }
}