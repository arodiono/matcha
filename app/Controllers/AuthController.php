<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use App\Services\IntraHelper;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

/**
 * Class AuthController
 * @package App\Controllers
 */
class AuthController extends Controller
{
    private $fb;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->fb = new Facebook([
            'app_id' => '1773360252722944',
            'app_secret' => '7b948af41043383139af1b6e54856281',
            'default_graph_version' => 'v2.2',
        ]);
    }

    public function getSignUpPhotos(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'signup/signup.photos.twig');
    }

    public function getSignUpInfo(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'signup/signup.info.twig', ['user' => Auth::user()->toArray()]);
    }

    public function postSignUpInfo(Request $request, Response $response): Response
    {
        $validation = $this->validator->validate($request, [
            'first_name' => v::noWhitespace()->notEmpty()->alpha(),
            'last_name' => v::noWhitespace()->notEmpty()->alpha(),
            'date' => v::noWhitespace()->notEmpty(),
            'gender' => v::notEmpty(),
            'sex_preference' => v::notEmpty(),
            'bio' => v::length(null, 150)
        ]);
        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('signup.info'));
        }

        $user = $this->auth->user();

//        ~r( $request->getParam('date'), new \DateTime($request->getParam('date')));

        $user->update([
            'first_name' => $request->getParam('first_name'),
            'last_name' => $request->getParam('last_name'),
            'date' => new \DateTime($request->getParam('date')),
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
        $helper = $this->fb->getRedirectLoginHelper();

        $permissions = ['email', 'user_photos'];
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $this->router->pathFor('fb-login');
        $loginUrl = $helper->getLoginUrl(htmlspecialchars($url), $permissions);

        return $this->view->render($response, 'signup/signin.twig', ['fb' => $loginUrl]);
    }

    public function facebookCallback(Request $request, Response $response): Response
    {
        $helper = $this->fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (\Exception $e) {
            return $this->view->render(
                $response->withStatus(400),
                'templates/error.twig',
                [
                    'status' => 404,
                    'message' => $e->getMessage()
                ]
            );
        }
        if (!isset($accessToken)) {
            return $this->view->render(
                $response->withStatus(400),
                'templates/error.twig',
                [
                    'status' => 404,
                    'message' => $helper->getError()
                ]
            );
        }


        try {
            $fbResponse = $this->fb->get('/me/?fields=first_name,last_name,email,gender', $accessToken);
//            $requestUserPhotos = $this->fb->get(htmlspecialchars('/me/albums?fields=photos{images}'), $accessToken);
        } catch (\Exception $e) {
            return $this->view->render(
                $response->withStatus(404),
                'templates/error.twig',
                [
                    'status' => 404,
                    'message' => $e->getMessage()
                ]
            );
        }

        $user = $fbResponse->getGraphUser();

        $exist = User::select('id')
            ->where('fb_id', '=', $user->getId())
            ->get()
            ->first();
        if (!empty($exist)) {
            $_SESSION['user'] = $exist->id;
            return $response->withRedirect($this->router->pathFor('home'));
        }

        $newUser = User::create([
            'username' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => password_hash(hash('md5', uniqid(rand(), true)), PASSWORD_DEFAULT),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'gender' => $user->getGender(),
            'fb_id' => $user->getId(),
            'hash' => hash('md5', uniqid(rand(), true))
        ]);

        $_SESSION['user'] = $newUser->id;
        $this->flash->addMessage('success', 'You have been signed up! Please tell about yourself');

        return $response->withRedirect($this->router->pathFor('signup.info'));
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

    public function intraSignUp(Request $request, Response $response): Response
    {
        $ic = new IntraHelper();
        $user = $ic->auth($request->getParam('code'), $request->getUri()->getBaseUrl() . $request->getUri()->getPath());
        $exist = User::select('id')
            ->where('fb_id', '=', $user->id)
            ->get()
            ->first();
        if (!empty($exist)) {
            $_SESSION['user'] = $exist->id;
            return $response->withRedirect($this->router->pathFor('home'));
        }
        $newUser = User::create([
            'username' => $user->id,
            'email' => $user->email,
            'password' => password_hash(hash('md5', uniqid(rand(), true)), PASSWORD_DEFAULT),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'fb_id' => $user->id,
            'hash' => hash('md5', uniqid(rand(), true))
        ]);
        $_SESSION['user'] = $newUser->id;
        $this->flash->addMessage('success', 'You have been signed up! Please tell about yourself');

        return $response->withRedirect($this->router->pathFor('signup.info'));
    }
}