<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 4/1/18
 * Time: 9:37 PM
 */

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\Notification;
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
        foreach ($notifications as $notification) {
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
}