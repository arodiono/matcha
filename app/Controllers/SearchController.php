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

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
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

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function advancedSearch(Request $request, Response $response): Response
    {
        $orderTypes = ['age', 'rating'];
        $orderDirections = ['asc', 'desc'];

        $blockedUsers = $this->blockModel->getBlockedUsersForUser(Auth::user()->id);
        $whereDateParams = array('from' => new \DateTime('1900-01-01'), 'to' => new \DateTime());
        $orderParams = ['type' => 'rating', 'order' => 'desc'];
        $tags = [];
        $location = ['radius' => 0, 'sort' => false];
        foreach ($request->getQueryParams() as $param => $value)
        {
            if ($param == 'age') {
                $from = new \DateTime();
                $to = new \DateTime();
                $whereDateParams['to'] = $from->sub(new \DateInterval('P' . explode(',', $value)[0] . 'Y'));
                $whereDateParams['from'] = $to->sub(new \DateInterval('P' . explode(',', $value)[1] . 'Y'));
                continue;
            }
            if ($param == 'order') {
                $data = explode(',', $value);
                if (in_array($data[0], $orderTypes) && in_array($data[1], $orderDirections)) {
                    $orderParams['type'] = $data[0] != 'age' ? 'users.' . $data[0] : 'users.date';
                    $orderParams['order'] = $data[1];
                } elseif ($data[0]  == 'location') {
                    $location['sort'] = true;
                }
                continue;
            }
            if ($param == 'tags') {
                $tags = explode(',', $value);
                continue;
            }
            if ($param == 'location') {
                $location['radius'] = $value;
                continue;
            }
        }
        if (empty($tags)){
            $rawData['users'] = User::select('*', 'users.id as s_user_id')
                ->whereDate('date', '>=', $whereDateParams['from'])
                ->whereDate('date', '<=', $whereDateParams['to'])
                ->whereNotIn('users.id', $blockedUsers)
                ->distinct()
                ->orderBy($orderParams['type'], $orderParams['order'])
                ->leftJoin('geoip', 'users.id', '=', 'geoip.user_id')
                ->get()
                ->all();
        } else {
            $rawData['users'] = User::from('tag_user')
                ->select('*', 'users.id as s_user_id')
                ->whereDate('users.date', '>=', $whereDateParams['from'])
                ->whereDate('users.date', '<=', $whereDateParams['to'])
                ->whereIn('tags.tag', $tags)
                ->whereNotIn('users.id', $blockedUsers)
                ->leftJoin('users', 'users.id', '=', 'tag_user.user_id')
                ->leftJoin('geoip', 'users.id', '=', 'geoip.user_id')
                ->leftJoin('tags', 'tag_user.tag_id', '=', 'tags.id')
                ->distinct()
                ->orderBy($orderParams['type'], $orderParams['order'])
                ->get()
                ->all();
        }
        $userIds = [];
        $data = [];
        $geo = new Location();
        $source = $geo->getCurrentCoords();
        foreach ($rawData['users'] as $user)
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
		foreach ($data['users'] as $key => $user)
        {
            if (in_array($user->id, $blockedUsers)) {
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