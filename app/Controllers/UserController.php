<?php

namespace App\Controllers;

use App\Models\Geoip;
use App\Models\Photo;
use App\Models\User;
use App\Models\Tag;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class UserController
 * @package App\Controllers
 */
class UserController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getUserProfile(Request $request, Response $response, $args): Response
    {
        $username = $args['name'];
        $user = User::where('username', $username)->first();
        if ($user === null) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('User not found');
        }
        $userdata = $user->getAttributes();
        $userdata['tags'] = Tag::where('user_id', $userdata['id'])->leftJoin('users-tags', 'tags.id', '=', 'users-tags.tag_id')->get()->toArray();
        $userdata['photos'] = Photo::where('user_id', $userdata['id'])->get()->toArray();
        return $this->view->render($response, 'profile.twig', $userdata);
    }

    public function setLocation(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (isset($data['latitude']) && isset($data['longitude'])) {
            Geoip::updateOrInsert(
                ['user_id' => $_SESSION['user']],
                ['user_id' => $_SESSION['user'], 'lat' => $data['latitude'], 'lon' => $data['longitude']]
            );
        }
        return $response->withStatus(200);
    }
}
