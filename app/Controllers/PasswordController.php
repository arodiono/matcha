<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

/**
 * Class PasswordController
 * @package App\Controllers
 */
class PasswordController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getChangePassword(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'password.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function postChangePassword(Request $request, Response $response): Response
    {
        $validation = $this->validator->validate($request, [
            'current_password' => v::noWhitespace()->notEmpty()->matchesPassword($this->auth->user()->password),
            'new_password' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('password.change'));
        }

        $this->auth->user()->setPassword($request->getParam('password_new'));
        $this->flash->addMessage('info', 'Password was changed.');

        return $response->withRedirect($this->router->pathFor('home'));
    }
}