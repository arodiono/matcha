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
//        $this->mailer->send('mail/template.twig', [], function($message){
//            $message->to('klymenok.a@gmail.com');
//            $message->subject('Email Subject');
//        });
        return $this->view->render($response, 'home.twig');
    }
}