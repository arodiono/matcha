<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\Block;
use App\Models\Location;
use App\Models\Tag;
use App\Models\User;
use App\Services\Search;
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

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getSuggestions(Request $request, Response $response): Response
    {
        $user = Auth::user();

        $blockedUsers = $this->blockModel->getBlockedUsersForUser($user->id);

        $data['users'] = User::whereIn('gender', $this->getSex()['gender'])->whereIn('sex_preference', $this->getSex()['preferences'])->whereNotIn('id', $blockedUsers)->get()->all();

        return $this->view->render($response, 'search/by.nearby.twig', $data);
    }

    /**
     * @return array sex preference
     */
    private function getSex() : array
    {
        $user = Auth::user();
        switch ($user->sex_preference) {
            case User::SEX_HETEROSEXUAL:
                $gender = $user->gender == User::GENDER_MALE ? User::GENDER_FEMALE : User::GENDER_MALE;
                return ['gender' => [$gender], 'preference' => [$user->sex_preference]];
                break;
            case User::SEX_HOMOSEXUAL:
                return ['gender' => [$user->gender], 'preference' => [$user->sex_preference]];
                break;
            default:
                return ['gender' => [User::GENDER_MALE, User::GENDER_FEMALE], 'preference' => [User::SEX_BISEXUAL, User::SEX_HETEROSEXUAL, User::SEX_HOMOSEXUAL]];
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function advancedSearch(Request $request, Response $response): Response
    {
        $params['blockedUsers'] = $this->blockModel->getBlockedUsersForUser(Auth::user()->id);
        $params['dateParams'] = array('from' => new \DateTime('1900-01-01'), 'to' => new \DateTime());
        $params['tags'] = [];
        $params['sex'] = $this->getSex();
        $location = ['radius' => 0, 'sort' => false];
        foreach ($request->getQueryParams() as $param => $value)
        {
            if ($param == 'age') {
                $from = new \DateTime();
                $to = new \DateTime();
                $params['dateParams']['to'] = $from->sub(new \DateInterval('P' . explode(',', $value)[0] . 'Y'));
                $params['dateParams']['from'] = $to->sub(new \DateInterval('P' . explode(',', $value)[1] . 'Y'));
            } elseif ($param == 'order') {
                $data = explode(',', $value);
                if ($data[0] == 'location') {
                    $location['sort'] = true;
                }
            } elseif ($param == 'tags') {
                $params['tags'] = explode(',', $value);
            } elseif ($param == 'location') {
                $location['radius'] = $value;
            }
        }
        $userIds = [];
        $data = [];
        $geo = new Location();
        $source = $geo->getCurrentCoords();
        foreach (Search::getFilteredUsers($params) as $user)
        {
            if (!in_array($user->s_user_id, $userIds)) {
                $userIds[] = $user->s_user_id;
                if ($location['radius'] !== 0) {
                    $user->distance = $geo->getDistance($source, ['lat' => $user->lat, 'lon' => $user->lon]);
                    if ($user->distance < $location['radius']) {
                        $data['users'][] = $user;
                    }
                } else {
                    $user->distance = $geo->getDistance($source, ['lat' => $user->lat, 'lon' => $user->lon]);
                    $data['users'][] = $user;
                }
            }
        }
        if ($location['sort']) {
            usort($data['users'], array($this, 'cmp'));
        }
        return $this->view->render($response, 'search/by.nearby.twig', $data);
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

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getNearbyUsers(Request $request, Response $response): Response
	{
		$geo = new Location();
		$location['radius'] = 42;
		if (array_key_exists('radius', $request->getParsedBody())) {
		    $location['radius'] = $request->getParsedBody()['radius'];
        }
		$blockedUsers = $this->blockModel->getBlockedUsersForUser(Auth::user()->id);
		$data['users'] = $geo->getUsers($location['radius']);
		$sex = $this->getSex();
		foreach ($data['users'] as $key => $user)
        {
            if (in_array($user->id, $blockedUsers)) {
                unset($data['users'][$key]);
            }
            elseif (!in_array($user->gender, $sex['gender'])) {
                unset($data['users'][$key]);
            } elseif (!in_array($user->sex_preference, $sex['preference'])) {
                unset($data['users'][$key]);
            }
        }
		return $this->view->render($response, 'search/by.nearby.twig', $data);
	}

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     *
     */
    public function getUsersByTag(Request $request, Response $response, $args): Response
    {
        $blockedUsers = $this->blockModel->getBlockedUsersForUser(Auth::user()->id);
        $data['users'] = Tag::find($args['id'])->users()->whereNotIn('users.id', $blockedUsers)->get()->all();
        return $this->view->render($response, 'search/by.tag.twig', $data);
    }
}