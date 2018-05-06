<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\Block;
use App\Models\Location;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class SearchController
 * @package App\Controllers
 */
class SearchController extends Controller
{

    protected $blockModel;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->blockModel = new Block();
    }

    public function getSuggestions(Request $request, Response $response): Response
    {
        $user = Auth::user();

        $blockedUsers = $this->blockModel->getBlockedUsersForUser($user->id);

        switch ($user->sex_preference) {
            case User::SEX_HETEROSEXUAL:
                $gender = $user->gender == User::GENDER_MALE ? User::GENDER_FEMALE : User::GENDER_MALE;
                $data['users'] = User::where('gender', $gender)->where('sex_preference', $user->sex_preference)->whereNotIn('id', $blockedUsers)->get()->all();
                break;
            case User::SEX_HOMOSEXUAL:
                $data['users'] = User::where('gender', $user->gender)->where('sex_preference', $user->sex_preference)->whereNotIn('id', $blockedUsers)->get()->all();
                break;
            default:
                $data['users'] = User::whereNotIn('id', $blockedUsers)->get()->all();
        }

        return $this->view->render($response, 'search/by.nearby.twig', $data);
    }

    public function advancedSearch(Request $request, Response $response): Response
    {
        $blockedUsers = $this->blockModel->getBlockedUsersForUser(Auth::user()->id);
        $whereParams = [];
        $tags = [];
        foreach ($request->getQueryParams() as $param => $value)
        {
            if ($param == 'age') {
                $whereParams[] = ['age', '>=', explode(',', $value)[0]];
                $whereParams[] = ['age', '<=', explode(',', $value)[1]];
                continue;
            }
            if ($param == 'tags') {
                $tags = explode(',', $value);
                continue;
            }
            $whereParams[] = ['users.' . $param, $value];
        }
        if (empty($tags)){
            $rawData['users'] = User::where($whereParams)->whereNotIn('users.id', $blockedUsers)->distinct()->get()->all();
        } else {
            $rawData['users'] = User::from('tag_user')->whereIn('tag_id', $tags)->where($whereParams)->whereNotIn('users.id', $blockedUsers)->join('users', 'users.id', '=', 'tag_user.user_id')->distinct()->get()->all();
        }
        $userIds = [];
        $data = [];
        foreach ($rawData['users'] as $user)
        {
            if (!in_array($user->id, $userIds)) {
                $userIds[] = $user->id;
                $data['users'][] = $user;
            }
        }
        return $this->view->render($response, 'search/by.nearby.twig', $data);
    }
    
    public function getNearbyUsers(Request $request, Response $response): Response
	{
		$geo = new Location();
		$blockedUsers = $this->blockModel->getBlockedUsersForUser(Auth::user()->id);
		$data['users'] = $geo->getUsers(999);
		foreach ($data['users'] as $key => $user)
        {
            if (in_array($user->id, $blockedUsers)) {
                unset($data['users'][$key]);
            }
        }

		return $this->view->render($response, 'search/by.nearby.twig', $data);
	}

    public function getUsersByTag(Request $request, Response $response, $args): Response
    {
        $blockedUsers = $this->blockModel->getBlockedUsersForUser(Auth::user()->id);
        $data['users'] = Tag::find($args['id'])->users()->whereNotIn('users.id', $blockedUsers)->get()->all();
        return $this->view->render($response, 'search/by.tag.twig', $data);
    }
}