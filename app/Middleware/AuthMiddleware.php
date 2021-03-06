<?php

namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class AuthMiddleware
 * @package App\Middleware
 */
class AuthMiddleware extends Middleware
{
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        if (!$this->auth->check()) {
            $this->flash->addMessage('warning', 'Please sing in before doing that.');
            return $response->withRedirect($this->router->pathFor('signin'));
        }
        $response = $next($request, $response);
        return $response;
    }
}