<?php

namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class OldInputMiddleware
 * @package App\Middleware
 */
class OldInputMiddleware extends Middleware
{
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $this->view->getEnvironment()->addGlobal('oldInput', $_SESSION['oldInput']);
        $_SESSION['oldInput'] = $request->getParams();
        $response = $next($request, $response);
        return $response;
    }
}