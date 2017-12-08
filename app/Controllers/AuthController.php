<?php

namespace App\Controllers;

use App\Models\User;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

class AuthController extends Controller
{
    public function getSignIn(Request $request, Response $response)
    {
        return $this->view->render($response, 'signin.twig');
    }

    public function postSignIn(Request $request, Response $response)
    {

    }

    public function getSignUp(Request $request, Response $response)
    {

        return $this->view->render($response, 'signup.twig');
    }

    public function postSignUp(Request $request, Response $response)
    {
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
            'firstname' => v::noWhitespace()->notEmpty()->alpha(),
            'password' => v::noWhitespace()->length(8, null),

        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('signup'));
        }

        User::create([
            'firstname' => $request->getParam('firstname'),
            'email' => $request->getParam('email'),
            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT)
        ]);
        return $response->withRedirect($this->router->pathFor('home'));
    }
}