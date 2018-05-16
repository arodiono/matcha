<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Services\IntraHelper;
use App\Services\Search;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Services\Intra;

/**
 * Class HomeController
 * @package App\Controllers
 */
class HomeController extends Controller
{

    public function __construct($container)
    {
        parent::__construct($container);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function index(Request $request, Response $response): Response
    {

        if (!Auth::check()) {
            return $this->view->render($response, 'landing.twig');
        }
        else {
            return $this->SearchController->getMainPageUsers($request, $response);
        }
    }
}