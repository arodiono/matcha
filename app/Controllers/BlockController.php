<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/27/18
 * Time: 11:10 PM
 */

namespace App\Controllers;


use App\Auth\Auth;
use App\Models\Block;
use App\Models\User;
use Slim\Http\Request;
use Slim\Http\Response;

class BlockController extends Controller
{
    private $model;

    private $user;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->model = new Block();
        $this->user = new User();
    }

    public function blockUser(Request $request, Response $response, $args): Response
    {
        $block = $request->getAttribute('name');
        if (Auth::user()->username == $block) {
            return $response->withRedirect($request->getUri()->getBaseUrl() . '/user/' . $block);
        }
        try {
            $this->model->blockUser(Auth::user()->id, $this->user->getId($block));
            return $response->withRedirect($request->getUri()->getBaseUrl() . '/user/' . $block);
        }
        catch (\Exception $e) {
            return $this->view->render($response, 'templates/error.twig', []);
        }
    }
}