<?php

namespace App\Controllers;

use App\Models\Tag;
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
    public function getSignUpPhotos(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'signup/signup.photos.twig');
    }

    public function getSignUpInfo(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'signup/signup.info.twig');
    }

    public function postSignUpInfo(Request $request, Response $response): Response
    {
        $validation = $this->validator->validate($request, [
            'first_name' => v::noWhitespace()->notEmpty()->alpha(),
            'last_name' => v::noWhitespace()->notEmpty()->alpha(),
            'gender' => v::notEmpty(),
            'sex_preference' => v::notEmpty(),
            'bio' => v::length(null, 150)
        ]);
        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('signup.info'));
        }

        $user = $this->auth->user();
        $user->update([
            'first_name' => $request->getParam('first_name'),
            'last_name' => $request->getParam('last_name'),
            'gender' => $request->getParam('gender'),
            'sex_preference' => $request->getParam('sex_preference'),
            'bio' => $request->getParam('bio')
        ]);

        $tags = explode(',', $request->getParam('tags'));

        foreach ($tags as $tag) {
            $tagId = Tag::updateOrCreate([
                'tag' => $tag
            ]);
            $user->tags()->attach($tagId->id);
        }

        $this->flash->addMessage('success', 'You have been signed up!');

        return $response->withRedirect($this->router->pathFor('signup.photos'));
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
        return $this->view->render($response, 'signup/signin.twig');
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
        return $this->view->render($response, 'signup/signup.twig');
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
            'username' => v::noWhitespace()->notEmpty()->alpha()->usernameAvailable(),
            'password' => v::noWhitespace()->length(8, null),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('signup'));
        }

        $user = User::create([
            'username' => $request->getParam('username'),
            'email' => $request->getParam('email'),
            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
            'hash' => hash('md5', uniqid(rand(), true))
        ]);

        $this->flash->addMessage('success', 'You have been signed up! Please tell about yourself');
        $this->auth->attempt($user->email, $request->getParam('password'));

        return $response->withRedirect($this->router->pathFor('signup.info'));
    }
}