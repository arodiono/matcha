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
        'hash',
        'fb_id'
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

    /**
     * @param $photo
     */
    public function setPhoto($photo)
    {
        $this->update([
            'profile_photo' => $photo
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne('App\Models\Location');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('App\Models\Tag')->using('App\Pivots\TagUserPivot');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
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

    /**
     * @param $username
     * @return int
     */
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

    /**
     * @param $id
     * @return bool
     */
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

    /**
     * @return array
     */
    public function getAllUsernamesAndIds() : array
    {
        return $this::select('id', 'username')
            ->get()
            ->toArray();
    }

    /**
     * @param int $userId
     * @param bool $status
     */
    public function setOnline(int $userId, bool $status)
    {
        $this::where('id', '=', $userId)
            ->update(['online' => $status]);
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function getOnline(int $userId) : bool
    {
        return $this::select('online')
            ->where('user_id', '=', $userId)
            ->get()
            ->online;
    }

    /**
     * @param int $who_id
     * @param int $whom_id
     */
    public function setVisit(int $who_id, int $whom_id)
    {
        $exist = $this::from('visits')->where('who_id', $who_id)
            ->where('whom_id', $whom_id)
            ->first();
        if (!$exist) {
            $this::from('visits')->insert(
                [
                    'who_id' => $who_id,
                    'whom_id' => $whom_id
                ]
            );
        } else {
            $this::from('visits')
                ->where('who_id', $who_id)
                ->where('whom_id', $whom_id)
                ->update(
                [
                    'who_id' => $who_id,
                    'whom_id' => $whom_id
                ]
            );
        }
    }

    /**
     * @param int $user_id
     * @param int $limit
     * @param string $type - can be 'me' or 'my', default - my
     * @return array
     */
    public function getUserVisits(int $user_id, string $type='my', int $limit=50): array
    {
        if ($type != 'my' && $type != 'me') {
            return [];
        }
        return $this::from('visits')
            ->select($type == 'my' ? 'whom_id' : 'who_id', 'updated_at')
            ->where($type == 'my' ? 'who_id' : 'whom_id', $user_id)
            ->orderBy('updated_at', 'DESC')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}