<?php

namespace App\Controllers;

use App\Models\User;
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

    public function getForgotPassword(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'password-forgot.twig');
    }

    public function postForgotPassword(Request $request, Response $response): Response
    {
        $validation = $this->validator->validate($request, [
            'email'=> v::email()->emailExist()
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('password.forgot'));
        }

        $email = $request->getParsedBodyParam('email');
        $user = User::where('email', $email)->first()->toArray();

        $this->mailer->send('mail/password-reset.twig', $user, function($message) use ($request, $email) {
            $message->to($email);
            $message->subject('[Matcha] Please reset your password');
        });

        $this->flash->addMessage('info', 'Check your email for a link to reset your password. If it doesn\'t appear within a few minutes, check your spam folder.');

        return $response->withRedirect($this->router->pathFor('password.forgot'));
    }

    public function getResetPassword(Request $request, Response $response, $args): Response
    {
		$user = User::where('hash', $args['hash'])->get();
		if (!$user->count()) {
			return $response->withStatus(404);
		}
        return $this->view->render($response, 'password-reset.twig', $args);
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
            return $response->withRedirect($this->router->pathFor('password.reset', $args));
        }
	
		$user->setPassword($request->getParsedBodyParam('password_repeat'));
		$user->update([
			'hash' => hash('md5', uniqid(rand(), true))
		]);
		
		$_SESSION['user'] = $user->id;
		
		$this->flash->addMessage('success', 'Your password has been changed successfully.');
        return $response->withRedirect($this->router->pathFor('home'));

    }

}