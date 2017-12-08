<?php

namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

class OldInputMiddleware extends Middleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        $this->container->view->getEnvironment()->addGlobal('oldInput', $_SESSION['oldInput']);
        $_SESSION['oldInput'] = $request->getParams();
        $response = $next($request, $response);
        return $response;
    }
}