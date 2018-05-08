<?php

namespace App\Controllers;
use App\Auth\Auth;
use App\Models\Like;
use App\Models\Notification;
use App\Models\Rating;
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
            Notification::setNotification($who->id, $whom->id, Notification::UNLIKE);
        }
        else {
            Like::create([
                'who_id' => $who->id,
                'whom_id' => $whom->id
            ]);
            if (Like::isMutually($who->id, $whom->id)) {
                Notification::setNotification($who->id, $whom->id, Notification::MUTUAL);
                Notification::setNotification($whom->id, $who->id, Notification::MUTUAL);
            } else {
                Notification::setNotification($who->id, $whom->id, Notification::LIKE);
            }
        }
        Rating::setRating(Auth::user()->id);
        return $response->withRedirect($this->router->pathFor('user.profile', ['name' => $whom->username]));
    }

    protected function mutualLike($who, $whom)
    {

    }
}