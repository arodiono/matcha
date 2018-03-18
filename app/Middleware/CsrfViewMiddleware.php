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

        $name = $this->csrf->getTokenName();
        $namekey = $this->csrf->getTokenNameKey();
        $value = $this->csrf->getTokenValue();
        $valuekey = $this->csrf->getTokenValueKey();

        $this->view->getEnvironment()->addGlobal('csrf', [
            'field' => '
                <input type="hidden" name="' . $namekey . '" value="' . $name . '">
                <input type="hidden" name="' . $valuekey . '" value="' . $value . '">
            ',
            'meta' => '
                <meta name="' . $namekey . '" content="' . $name . '">
                <meta name="' . $valuekey . '" content="' . $value . '">
            ',
        ]);
        $response = $next($request, $response);
        return $response;
    }
}