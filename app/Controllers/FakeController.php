<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 5/23/18
 * Time: 10:03 PM
 */

namespace App\Controllers;


use App\Auth\Auth;
use App\Models\Fake;
use App\Models\User;
use Slim\Http\Request;
use Slim\Http\Response;

class FakeController extends Controller
{

    private $user;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->user = new User();
    }

    public function setFakeUser(Request $request, Response $response, $args): Response
    {
        try {
            $fake = $request->getAttribute('name');
            if (Auth::user()->username == $fake) {
                return $response->withRedirect($request->getUri()->getBaseUrl() . '/user/' . $fake);
            }
            Fake::insert(
                [
                    "reporter" => Auth::user()->id,
                    "fake" => $this->user->getId($fake)
                ]
            );
            return $response->withRedirect($request->getUri()->getBaseUrl() . '/user/' . $fake);
        } catch (\Exception $e) {
            return $this->view->render($response, 'templates/error.twig', []);
        }
    }
}