<?php

namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class CsrfViewMiddleware
 * @package App\Middleware
 */
class CsrfViewMiddleware extends Middleware
{
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $this->view->getEnvironment()->addGlobal('csrf', [
            'field' => '
                <input type="hidden" name="' . $this->csrf->getTokenNameKey() . '" value="' . $this->csrf->getTokenName() . '">
                <input type="hidden" name="' . $this->csrf->getTokenValueKey() . '" value="' . $this->csrf->getTokenValue() . '">
            ',
            'meta' => '
                <meta name="' . $this->csrf->getTokenNameKey() . '" content="' . $this->csrf->getTokenName() . '">
                <meta name="' . $this->csrf->getTokenValueKey() . '" content="' . $this->csrf->getTokenValue() . '">
            ',
        ]);
        $response = $next($request, $response);
        return $response;
    }
}