<?php

namespace App\Models;

use App\Auth\Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * @property mixed id
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

    public function conversations()
    {
        return $this->hasMany('App\Models\Conversation', 'user_id_1')
            ->where('user_id_1', '=', $this->id)
            ->orWhere('user_id_2', '=', $this->id);
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

    public function getUsernameById($id)
    {
        $data = $this::select('username')
            ->where('id', '=', $id)
            ->get()
            ->first();
        if (!empty($data)) {
            return $data->toArray()['username'];
        } else {
            return false;
        }
    }

    public function getAllUsernamesAndIds() : array
    {
        return $this::select('id', 'username')
            ->get()
            ->toArray();
    }

    public function setOnline(int $userId, bool $status)
    {
        $this::where('id', '=', $userId)
            ->update(['online' => $status]);
    }

    public function getOnline(int $userId) : bool
    {
        return $this::select('online')
            ->where('user_id', '=', $userId)
            ->get()
            ->online;
    }

    public function setActivity(string $name, int $user_id, int $activity_user_id)
    {
        $this::from('activity')->insert(
            [
                'user_id' => $user_id,
                'activity_user_id' => $activity_user_id,
                'activity_type' => $this::from('activity_type')
                    ->select('id')
                    ->where('activity_name', $name)
                    ->get()
                    ->first()['id']
            ]
        );
    }

    public function getUserActivity(string $name, int $user_id, int $limit=50): array
    {
        return $this::from('activity')
            ->select('activity_user_id', 'activity.created_at')
            ->leftJoin('activity_type', 'activity.activity_type', '=', 'activity_type.id')
            ->where('activity_type.activity_name', $name)
            ->where('activity.user_id', $user_id)
            ->orderBy('activity.id', 'DESC')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}