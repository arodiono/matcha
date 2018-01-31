<?php

namespace App\Controllers;

use App\Models\Geoip;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class SearchController
 * @package App\Controllers
 */
class SearchController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
//    public function index(Request $request, Response $response): Response
//    {
//
//		$geo = new Geoip();
//		$geo->getUsers(999);
//        return $this->view->render($response, 'home.twig');
//    }
    
    public function getNearbyUsers(Request $request, Response $response): Response
	{
		$geo = new Geoip();
		$data['users'] = $geo->getUsers(999);
//		r($data);
//		die();
		return $this->view->render($response, 'search-nearby.twig', $data);
	}
}