<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 4/1/18
 * Time: 9:37 PM
 */

namespace App\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Slim\Http\Request;
use Slim\Http\Response;

class NotificationController extends Controller
{

    private $notificationModel;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->notificationModel = new Notification();
    }

    public function getNotifications(Request $request, Response $response, $args): Response
    {
//        if (!array_key_exists('name', $args) || !array_key_exists('user', $_SESSION) || !$this->userModel->isUserExist($args['name'])) {
//            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('User not found');
//        }
        $notifications = Notification::where('whom_id', $_SESSION['user'])->with('who')->get()->all();
        $ids = [];
        foreach ($notifications as $notification)
        {
            $ids[] = $notification->id;
        }
        $this->notificationModel->setHasBeenRead($ids);
//        ~r($notifications[0]->who()->first());
        return $this->view->render(
            $response,
            'user/notifications.twig',
            [
                'notifications' => $notifications
            ]
        );
    }

    public function postNotification(Request $request, Response $response, $args): Response
    {
        $body = $request->getParsedBody();
        if (!array_key_exists('user', $body) || !array_key_exists('type', $body)) {
            return $response->withStatus(401);
        }
        $receiver = $body['user'];
        $sender = $_SESSION['user'];
        if ($receiver === $sender) {
            return $response->withStatus(504);
        }
        if ($this->notificationModel->setNotification($sender, $receiver, $body['type'])) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(504);
        }
    }
}