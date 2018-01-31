<?php

namespace App\Controllers;

use App\Models\Geoip;
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
//        $this->mailer->send('mail/template.twig', [], function($message){
//            $message->to('klymenok.a@gmail.com');
//            $message->subject('Email Subject');
//        });
		$geo = new Geoip();
		$geo->getUsers(999);
        return $this->view->render($response, 'home.twig');
    }
}