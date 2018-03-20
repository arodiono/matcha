<?php

namespace App\Controllers;

use Respect\Validation\Exceptions\MaxException;
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
        return $this->view->render($response, 'messages/message.twig', ['data' => $messages, 'user' => $user->getUsernameById($_SESSION['user'])]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */

    public function postMessage(Request $request, Response $response, $args): Response
    {
        $body = $request->getParsedBody();
        if (!array_key_exists('text', $body)) {
            return $response->withStatus(401);
        }
        $user = new User();
        $message = new Message();
        $pathArray  = explode('/', $request->getUri()->getPath());
        $receiver = $user->getId(array_last($pathArray));
        $sender = $_SESSION['user'];
        $text = $request->getParsedBody()['text'];
        if ($message->setMessage($sender, $receiver, $text)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(504);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */

    public function setMessageHasBeenRead(Request $request, Response $response, $args): Response
    {
        $user = new User();
        $message = new Message();
        $pathArray  = explode('/', $request->getUri()->getPath());
        $senderId = $user->getId($pathArray[count($pathArray) - 2]);
        $parsedBody = $request->getParsedBody();
        if (array_key_exists('user', $parsedBody)) {
            $receiverName = $parsedBody['user'];
            if ($message->setMessagesAsHasBeenRead($senderId, $user->getId($receiverName))) {
                return $response->withStatus(200);
            }
        }
        return $response->withStatus(504);
    }
}