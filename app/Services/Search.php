<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 5/14/18
 * Time: 7:41 PM
 */

namespace App\Services;

use App\Auth\Auth;
use App\Models\Block;
use App\Models\Location;
use App\Models\User;

class Search
{

    protected $user;

    protected $blocked;

    protected $geo;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->blockModel = new Block();
        $this->blocked = $this->blockModel->getBlockedUsersForUser($this->user->id);
        $this->geo = new Location();
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFilteredUsers(array $params) : array
    {
        $sex = $this->getSex();
        if (empty($params['tags'])) {
            return User::select('*', 'users.id as s_user_id')
                ->whereDate('users.date', '>=', $params['dateParams']['from'])
                ->whereDate('users.date', '<=', $params['dateParams']['to'])
                ->whereIn('users.gender', $sex['gender'])
                ->whereIn('users.sex_preference', $sex['preference'])
                ->whereNotIn('users.id', $this->blocked)
                ->distinct()
                ->orderBy('users.rating', 'desc')
                ->leftJoin('geoip', 'users.id', '=', 'geoip.user_id')
                ->get()
                ->all();
        } else {
            return User::from('tag_user')
                ->select('*', 'users.id as s_user_id')
                ->whereDate('users.date', '>=', $params['dateParams']['from'])
                ->whereDate('users.date', '<=', $params['dateParams']['to'])
                ->whereIn('users.gender', $sex['gender'])
                ->whereIn('users.sex_preference', $sex['preference'])
                ->whereIn('tags.tag', $params['tags'])
                ->whereNotIn('users.id', $this->blocked)
                ->leftJoin('users', 'users.id', '=', 'tag_user.user_id')
                ->leftJoin('geoip', 'users.id', '=', 'geoip.user_id')
                ->leftJoin('tags', 'tag_user.tag_id', '=', 'tags.id')
                ->distinct()
                ->orderBy('users.rating', 'desc')
                ->get()
                ->all();
        }
    }

    /**
     * @return array sex preference
     */
    public function getSex() : array
    {
        switch ($this->user->sex_preference) {
            case User::SEX_HETEROSEXUAL:
                $gender = $this->user->gender == User::GENDER_MALE ? User::GENDER_FEMALE : User::GENDER_MALE;
                return ['gender' => [$gender], 'preference' => [$this->user->sex_preference]];
                break;
            case User::SEX_HOMOSEXUAL:
                return ['gender' => [$this->user->gender], 'preference' => [$this->user->sex_preference]];
                break;
            default:
                return ['gender' => [User::GENDER_MALE, User::GENDER_FEMALE], 'preference' => [User::SEX_BISEXUAL, User::SEX_HETEROSEXUAL, User::SEX_HOMOSEXUAL]];
        }
    }

    /**
     * @return array
     */
    public function getSuggestions() : array
    {
        return User::whereIn('gender', $this->getSex()['gender'])
            ->whereIn('sex_preference', $this->getSex()['preference'])
            ->whereNotIn('id', $this->blocked)
            ->get()
            ->all();
    }

    /**]
     * @param int $limit
     * @return array
     */
    public function getPopularUsers(int $limit=6) : array
    {
        return User::whereIn('gender', $this->getSex()['gender'])
            ->whereIn('sex_preference', $this->getSex()['preference'])
            ->whereNotIn('id', $this->blocked)
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getNearestUsers(int $limit=6) : array
    {
        $users = User::whereIn('gender', $this->getSex()['gender'])
            ->whereIn('sex_preference', $this->getSex()['preference'])
            ->whereNotIn('id', $this->blocked)
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get()
            ->all();
        $source = $this->geo->getCurrentCoords();
        foreach ($users as $user)
        {
            $user->distance = $this->geo->getDistance($source, ['lat' => $user->lat, 'lon' => $user->lon]);
        }
        usort($users, array($this, 'cmp'));
        return array_slice($users, 0, $limit);
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getNewestUsers(int $limit=6) : array
    {
        return User::whereIn('gender', $this->getSex()['gender'])
            ->whereIn('sex_preference', $this->getSex()['preference'])
            ->whereNotIn('id', $this->blocked)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
    * @param $a User object
    * @param $b User object
    * @return bool
    *
    * method provide comparing of 2 user objects by distance attribute
    */
    private function cmp($a, $b) {
        return $a->distance > $b->distance;
    }
}