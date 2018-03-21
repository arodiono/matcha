<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/20/18
 * Time: 10:32 PM
 */

namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

class ConversationMiddleware extends Middleware
{
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $response = $next($request, $response);
        return $response;
    }
}