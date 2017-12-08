<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class HomeController
 * @package App\Controllers
 */
class HomeController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'home.twig');
    }
}