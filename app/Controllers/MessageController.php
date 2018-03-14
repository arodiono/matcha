<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;


class MessageController extends Controller
{
    public function getMessage(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'messages/message.twig');
    }

    public function postMessage(Request $request, Response $response): Response
    {
        return $response->withRedirect($this->router->pathFor('signup/signup.info'));
    }
}