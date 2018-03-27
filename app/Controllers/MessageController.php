<?php

namespace App\Controllers;

use App\Models\Conversation;
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

    private $conversationsModel;

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
        $this->conversationsModel = new Conversation();
    }

    public function getAllConversations(Request $request, Response $response, $args): Response
    {
        if (!array_key_exists('user', $_SESSION)) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('User not found');
        }
        $convRawData = $this->conversationsModel->getAllConversations($_SESSION['user']);
        $usersRawData = $this->userModel->getAllUsernamesAndIds();
        $users = [];
        foreach ($usersRawData as $item)
        {
            $users[$item['id']] = $item['username'];
        }
        $conversations = [];
        foreach ($convRawData as $item)
        {
            $username = $item['user_id_1'] == $_SESSION['user'] ? $users[$item['user_id_2']] : $users[$item['user_id_1']];
            $conversations[] = ['username' =>$username, 'msg' => $item['last_message']];
        }
        return $this->view->render(
            $response, 'messages/all.twig',
            ['conversations' => $conversations]
        );
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */

    public function getMessages(Request $request, Response $response, $args): Response
    {
        if (!array_key_exists('name', $args) || !array_key_exists('user', $_SESSION) || !$this->userModel->isUserExist($args['name'])) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('User not found');
        }
        $interlocutor = $this->userModel->getId($args['name']);
        if ($interlocutor === $_SESSION['user']){
            return $response->withStatus(404);
        }
        return $this->view->render(
            $response,
            'messages/message.twig',
            [
                'messages' => $this->messageModel->getMessageHistory($_SESSION['user'], $interlocutor),
                'current_user' => $this->userModel->getUsernameById($_SESSION['user']),
                'interlocutor' => User::find($interlocutor)
            ]
        );
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