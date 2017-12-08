<?php

namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GuestMiddleware
 * @package App\Middleware
 */
class GuestMiddleware extends Middleware
{
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        if ($this->auth->check()) {
            return $response->withRedirect($this->router->pathFor('home'));
        }
        $response = $next($request, $response);
        return $response;
    }
}