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
use Slim\Http\Request;
use Slim\Http\Response;

class BlockController extends Controller
{
    private $model;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->model = new Block();
    }

    public function blockUser(Request $request, Response $response, $args): Response
    {
        $body = $request->getParsedBody();
        if (!array_key_exists('user_id_to_block', $body)) {
            return $response->withStatus(401);
        }
        $this->model->blockUser(Auth::user()->id, $body['user_id_to_block']);
        return $response->withStatus(200);
    }
}