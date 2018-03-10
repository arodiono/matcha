<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\Location;
use App\Models\Tag;
use App\Models\User;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class SearchController
 * @package App\Controllers
 */
class SearchController extends Controller
{

    public function getSuggestions(Request $request, Response $response): Response
    {
        $user = Auth::user();

        switch ($user->sex_preference) {
            case User::SEX_HETEROSEXUAL:
                $gender = $user->gender == User::GENDER_MALE ? User::GENDER_FEMALE : User::GENDER_MALE;
                $data['users'] = User::where('gender', $gender)->where('sex_preference', $user->sex_preference)->get()->all();
                break;
            case User::SEX_HOMOSEXUAL:
                $data['users'] = User::where('gender', $user->gender)->where('sex_preference', $user->sex_preference)->get()->all();
                break;
            default:
                $data['users'] = User::get()->all();
        }

        return $this->view->render($response, 'search/by.nearby.twig', $data);
    }
    
    public function getNearbyUsers(Request $request, Response $response): Response
	{
		$geo = new Location();
		$data['users'] = $geo->getUsers(999);

		return $this->view->render($response, 'search/by.nearby.twig', $data);
	}

    public function getUsersByTag(Request $request, Response $response, $args): Response
    {
        $data['users'] = Tag::find($args['id'])->users()->get()->all();
        return $this->view->render($response, 'search/by.tag.twig', $data);
    }
}