<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\Like;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Photo;
use App\Models\Rating;
use App\Models\User;
use App\Models\Tag;
use App\Models\Visit;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

/**
 * Class UserController
 * @package App\Controllers
 */
class UserController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getUserProfile(Request $request, Response $response, $args): Response
    {
        $user = User::where('username', $args['name'])->with('photos', 'tags')->first();
        if ($user === null) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('User not found');
        }

        $data = [
            'user' => $user,
            'isLiked' => Like::isExist(Auth::user()->id, $user->id),
            'isMutually' => Like::isMutually(Auth::user()->id, $user->id)
        ];
        if (Auth::user()->id !== $user->id) {
            Notification::setNotification(Auth::user()->id, $user->id, Notification::VISIT);
        }
        return $this->view->render($response, 'user/profile.twig', $data);
    }

    public function setLocation(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            Auth::user()->location()->updateOrInsert(['user_id' => $_SESSION['user']],
                [
                'user_id' => $_SESSION['user'],
                'lat' => $data['latitude'],
                'lon' => $data['longitude']
                ]);
        }
        return $response->withStatus(200);
    }
    
    public function getUserEdit(Request $request, Response $response): Response
	{
	    $user = User::where('id', $_SESSION['user'])->with('photos')->first();
		return $this->view->render($response, 'user/profile.edit.twig', ['user' => $user]);
	}
	
	public function postUserEdit(Request $request, Response $response): Response
	{
        $data = $request->getParsedBody();

        $user = Auth::user();

        $user->update([
            'email' => htmlspecialchars($data['email']),
            'first_name' => htmlspecialchars($data['first_name']),
            'last_name' => htmlspecialchars($data['last_name']),
            'gender' => htmlspecialchars($data['gender']),
            'sex_preference' => htmlspecialchars($data['sex_preference']),
            'bio' => htmlspecialchars($data['bio'])
        ]);

        $this->flash->addMessage('success', 'Information successfully updated.');
        return $response->withRedirect($this->router->pathFor('user.edit'));

    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getChangePassword(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'user/password.change.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function postChangePassword(Request $request, Response $response): Response
    {
        $validation = $this->validator->validate($request, [
            'current_password' => v::noWhitespace()->notEmpty(),
            'new_password' => v::noWhitespace()->notEmpty()->matchesOldPassword($this->auth->user()->password),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('user.password.change'));
        }

        $this->auth->user()->setPassword($request->getParam('new_password'));
        $this->flash->addMessage('info', 'Password was changed.');

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getForgotPassword(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'user/password.forgot.twig');
    }

    public function postForgotPassword(Request $request, Response $response): Response
    {
        $validation = $this->validator->validate($request, [
            'email'=> v::email()->emailExist()
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('user.password.forgot'));
        }

        $email = $request->getParsedBodyParam('email');
        $user = User::where('email', $email)->first()->toArray();

        $this->mailer->send('mail/password.reset.twig', $user, function($message) use ($request, $email) {
            $message->to($email);
            $message->subject('[Matcha] Please reset your password');
        });

        $this->flash->addMessage('info', 'Check your email for a link to reset your password. If it doesn\'t appear within a few minutes, check your spam folder.');

        return $response->withRedirect($this->router->pathFor('user.password.forgot'));
    }

    public function getResetPassword(Request $request, Response $response, $args): Response
    {
        $user = User::where('hash', $args['hash'])->get();
        if (!$user->count()) {
            return $response->withStatus(404);
        }
        return $this->view->render($response, 'user/password.reset.twig', $args);
    }

    public function postResetPassword(Request $request, Response $response, $args): Response
    {

        $user = User::where('hash', $args['hash'])->first();

        if (!$user->count()) {
            return $response->withStatus(404);
        }

        $validation = $this->validator->validate($request, [
            'password' => v::noWhitespace()->notEmpty()
                ->matchesOldPassword($user->password),
            'password_repeat' => v::noWhitespace()->notEmpty()
                ->matchesNewPassword($request->getParsedBodyParam('password')),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('user.password.reset', $args));
        }

        $user->setPassword($request->getParsedBodyParam('password_repeat'));
        $user->update([
            'hash' => hash('md5', uniqid(rand(), true))
        ]);

        $_SESSION['user'] = $user->id;

        $this->flash->addMessage('success', 'Your password has been changed successfully.');
        return $response->withRedirect($this->router->pathFor('home'));

    }

    public function postDeleteUser(Request $request, Response $response): Response
    {
        Auth::user()->delete();
        session_destroy();
        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function postOnline(Request $request, Response $response) : Response
    {
        $user = new User();
        $username = $request->getParam('username');
        $status = $request->getParam('status');
        $user->setOnline($user->getId($username), $status);
        return $response->withStatus(200);
    }
}
