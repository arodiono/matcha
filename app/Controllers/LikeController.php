<?php

namespace App\Controllers;
use App\Auth\Auth;
use App\Models\Like;
use App\Models\User;
use Slim\Http\Response;
use Slim\Http\Request;

/**
 * Class LikeController
 * @property mixed view
 * @package App\Controllers
 */
class LikeController extends Controller
{

    public function toggleLike(Request $request, Response $response, $args): Response
    {
        $who = Auth::user();
        $whom = User::find($args['id']);

        $like = Like::where('who_id', $who->id)->where('whom_id', $whom->id)->first();

        if ($like !== null) {
            $like->delete();
        }
        else {
            Like::create([
                'who_id' => $who->id,
                'whom_id' => $whom->id
            ]);
        }

        return $response->withRedirect($this->router->pathFor('user.profile', ['name' => $whom->username]));
    }
}