<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\User;
use App\Models\Message;

/**
 * Class MessageController
 * @package App\Controllers
 */

class MessageController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */

    public function getMessage(Request $request, Response $response, $args): Response
    {
        $user = new User();

        if (!array_key_exists('name', $args) || !array_key_exists('user', $_SESSION) || !$user->isUserExist($args['name'])) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('User not found');
        }
        $user_id = $user->getId($args['name']);
        $messageBase = new Message();
        $messages = $messageBase->getMessageHistory($_SESSION['user'], $user_id);
        return $this->view->render($response, 'messages/message.twig', ['data' => $messages]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */

    public function postMessage(Request $request, Response $response): Response
    {
        return $response->withRedirect($this->router->pathFor('signup/signup.info'));
    }
}