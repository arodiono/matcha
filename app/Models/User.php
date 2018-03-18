<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * @package App\Models
 */
class User extends Model
{
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    public const SEX_HETEROSEXUAL = 'heterosexual';
    public const SEX_HOMOSEXUAL = 'homosexual';
    public const SEX_BISEXUAL = 'bisexual';

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

    public function location()
    {
        return $this->hasOne('App\Models\Location');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Models\Tag')->using('App\Pivots\TagUserPivot');
    }

    public function photos()
    {
        return $this->hasMany('App\Models\Photo');
    }

    /**
     * @param $user
     * @return bool
     *
     */

    public function isUserExist($user): Bool
    {
        $data = $this::select('id')
        ->where('username', '=', $user)
        ->get()
        ->first();
        if (!empty($data)) {
            return true;
        }
        return false;
    }

    public function getId($username) : int
    {
        $data = $this::select('id')
            ->where('username', '=', $username)
            ->get()
            ->first();
        if (!empty($data)) {
            return $data->toArray()['id'];
        } else {
            return 0;
        }
    }
}