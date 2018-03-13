<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/13/18
 * Time: 8:32 PM
 */

namespace App\Services;


use Ratchet\Server\IoServer;

require '/Users/oklymeno/matcha-api/vendor/autoload.php';

$server = IoServer::factory(
    new MessageService(),
    8000
);

$server->run();