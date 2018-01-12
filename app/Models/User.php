<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * @package App\Models
 */
class User extends Model
{
    /**
     * @var string
     */
    protected $table = 'users';
    /**
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'username',
        'first_name',
        'last_name',
        'gender',
        'sex_preference',
        'bio',
        'profile_photo',
        'hash'
    ];

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->update([
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    public function setPhoto($photo)
    {
        $this->update([
            'profile_photo' => $photo
        ]);
    }
}