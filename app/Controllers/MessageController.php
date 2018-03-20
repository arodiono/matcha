<?php

namespace App\Controllers;

use App\Models\Conversation;
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
    private $messageModel;

    private $userModel;

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */

    public function __construct($container)
    {
        parent::__construct($container);
        $this->messageModel = new Message();
        $this->userModel = new User();
    }

    public function getAllConversations(Request $request, Response $response, $args): Response
    {
        if (!array_key_exists('user', $_SESSION)) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('User not found');
        }
        $conversations = $this->messageModel->getAllConversationsWithMassages($_SESSION['user']);


        return $this->view->render($response, 'messages/all.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */

    public function getMessage(Request $request, Response $response, $args): Response
    {
        if (!array_key_exists('name', $args) || !array_key_exists('user', $_SESSION) || !$this->userModel->isUserExist($args['name'])) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('User not found');
        }
        $user_id = $this->userModel->getId($args['name']);
        if ($user_id === $_SESSION['user']){
            return $response->withStatus(404);
        }
        $messages = $this->messageModel->getMessageHistory($_SESSION['user'], $user_id);
        return $this->view->render($response, 'messages/message.twig', ['data' => $messages, 'user' => $this->userModel->getUsernameById($_SESSION['user'])]);
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
        $pathArray  = explode('/', $request->getUri()->getPath());
        $receiver = $this->userModel->getId(array_last($pathArray));
        $sender = $_SESSION['user'];
        $text = $request->getParsedBody()['text'];
        if ($receiver === $sender || $text == '') {
            return $response->withStatus(504);
        }
        if ($this->messageModel->setMessage($sender, $receiver, $text)) {
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
        $pathArray  = explode('/', $request->getUri()->getPath());
        $senderId = $this->userModel->getId($pathArray[count($pathArray) - 2]);
        $parsedBody = $request->getParsedBody();
        if (array_key_exists('user', $parsedBody)) {
            $receiverName = $parsedBody['user'];
            if ($this->messageModel->setMessagesAsHasBeenRead($senderId, $this->userModel->getId($receiverName))) {
                return $response->withStatus(200);
            }
        }
        return $response->withStatus(504);
    }
}