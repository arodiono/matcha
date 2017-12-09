<?php

namespace App\Controllers;

use App\Models\User;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

/**
 * Class AuthController
 * @package App\Controllers
 */
class AuthController extends Controller
{
    public function getSignUpFinish(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'signup-finish.twig');
    }

    public function postSignUpFinish(Request $request, Response $response): Response
    {
//        $validation = $this->validator->validate($request, [
//            'email' => v::email()->emailAvailable(),
//            'first_name' => v::noWhitespace()->notEmpty()->alpha(),
//            'password' => v::noWhitespace()->length(8, null),
//
//        ]);
//
//        if ($validation->failed()) {
//            return $response->withRedirect($this->router->pathFor('signup'));
//        }
//
//        $user = User::create([
//            'first_name' => $request->getParam('first_name'),
//            'email' => $request->getParam('email'),
//            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT)
//        ]);
//
//        $this->flash->addMessage('success', 'You have been signed up!');
//        $this->auth->attempt($user->email, $request->getParam('password'));
//
//        return $response->withRedirect($this->router->pathFor('home'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getSignOut(Request $request, Response $response): Response
    {
        $this->auth->logout();
        return $response->withRedirect($this->router->pathFor('home'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getSignIn(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'signin.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function postSignIn(Request $request, Response $response): Response
    {
        $auth = $this->auth->attempt(
            $request->getParam('email'),
            $request->getParam('password')
        );
        if (!$auth) {
            $this->flash->addMessage('error', 'Incorrect username or password.');
            return $response->withRedirect($this->router->pathFor('signin'));
        }
        return $response->withRedirect($this->router->pathFor('home'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getSignUp(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'signup.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function postSignUp(Request $request, Response $response): Response
    {
        $validation = $this->validator->validate($request, [
            'email' => v::email()->emailAvailable(),
            'username' => v::noWhitespace()->notEmpty()->alpha(),
            'password' => v::noWhitespace()->length(8, null),

        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('signup'));
        }

        $user = User::create([
            'username' => $request->getParam('username'),
            'email' => $request->getParam('email'),
            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT)
        ]);

        $this->flash->addMessage('success', 'You have been signed up!');
        $this->auth->attempt($user->email, $request->getParam('password'));

        return $response->withRedirect($this->router->pathFor('signup.finish'));
    }
}