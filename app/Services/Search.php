<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 5/14/18
 * Time: 7:41 PM
 */

namespace App\Services;

use App\Models\User;

class Search
{
    public static function getFilteredUsers(array $params) : array
    {
        if (empty($params['tags'])) {
            return User::select('*', 'users.id as s_user_id')
                ->whereDate('date', '>=', $params['dateParams']['from'])
                ->whereDate('date', '<=', $params['dateParams']['to'])
                ->whereIn('users.gender', $params['sex']['gender'])
                ->whereIn('users.sex_preference', $params['sex']['preference'])
                ->whereNotIn('users.id', $params['blockedUsers'])
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
                ->whereIn('users.gender', $params['sex']['gender'])
                ->whereIn('users.sex_preference', $params['sex']['preference'])
                ->whereIn('tags.tag', $params['tags'])
                ->whereNotIn('users.id', $params['blockedUsers'])
                ->leftJoin('users', 'users.id', '=', 'tag_user.user_id')
                ->leftJoin('geoip', 'users.id', '=', 'geoip.user_id')
                ->leftJoin('tags', 'tag_user.tag_id', '=', 'tags.id')
                ->distinct()
                ->orderBy('users.rating', 'desc')
                ->get()
                ->all();
        }
    }
}