<?php

use Respect\Validation\Validator as v;
use Slim\Http\Request;
use Slim\Http\Response;
use Tinify\Tinify;

session_start();
//date_default_timezone_set('Europe/Kiev');
date_default_timezone_set('UTC');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/config.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'db' => [
            'driver' => DB_DRIVER,
            'host' => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PASS,
            'database' => DB_NAME,
            'charset' => DB_CHARSET,
            'collation' => DB_COLLATION
        ],
        'logger' => [
            'name' => 'matcha',
            'level' => Monolog\Logger::DEBUG,
            'path' => __DIR__ . '/../var/logs/app.log',
        ],
    ]
]);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['uploads'] = __DIR__ . '/../public/uploads';
$container['db'] = function () use ($capsule) {
    return $capsule;
};
$container['auth'] = function () {
    return new \App\Auth\Auth;
};
$container['flash'] = function () {
    return new \Slim\Flash\Messages;
};
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/Views', [
        'cache' => false,
        'debug' => true
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));
    $view->addExtension(new Twig_Extension_Debug());
    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user' => $container->auth->user(),
    ]);
    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;
};

$container['validator'] = function () {
    return new App\Validation\Validator;
};
$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};
$container['AuthController'] = function ($container) {
    return new \App\Controllers\AuthController($container);
};
$container['LikeController'] = function ($container) {
    return new \App\Controllers\LikeController($container);
};
$container['PhotoController'] = function ($container) {
    return new \App\Controllers\PhotoController($container);
};
$container['UserController'] = function ($container) {
    return new \App\Controllers\UserController($container);
};
$container['SearchController'] = function ($container) {
	return new \App\Controllers\SearchController($container);
};
$container['MessageController'] = function ($container) {
    return new \App\Controllers\MessageController($container);
};
$container['NotificationController'] = function ($container) {
    return new \App\Controllers\NotificationController($container);
};
$container['FakeController'] = function ($container) {
    return new \App\Controllers\FakeController($container);
};
$container['BlockController'] = function ($container) {
    return new \App\Controllers\BlockController($container);
};
$container['csrf'] = function () {
    return new \Slim\Csrf\Guard;
};
$container['logger'] = function () {
    return new \Monolog\Logger('matcha');
};
$container['logger']->pushHandler(new Monolog\Handler\StreamHandler('../var/logs/logs.log', \Monolog\Logger::WARNING));

$container['mailer'] = function ($container) {
    $mailer = new \PHPMailer\PHPMailer\PHPMailer();

    $mailer->isSMTP();
    $mailer->setFrom('matcha.kyiv@gmail.com', 'Matcha');

    $mailer->Host = 'smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->SMTPSecure = 'tls';
    $mailer->Port = 587;
    $mailer->Username = 'matcha.kyiv@gmail.com';
    $mailer->Password = MAIL_PASS;
    $mailer->addCustomHeader('Content-Type', 'text/html');

	return new \App\Models\Mail($container->view, $mailer);
};


$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
//$app->add(new \App\Middleware\CsrfViewMiddleware($container));
//$app->add($container->get('csrf'));
$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        $uri = $uri->withPath(substr($path, 0, -1));
        if ($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        } else {
            return $next($request->withUri($uri), $response);
        }
    }
    return $next($request, $response);
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

v::with('App\\Validation\\Rules\\');
Tinify::setKey('Q0fPpkfgcREXv52XL1NHvP2LupHokyDw');

require __DIR__ . '/routes.php';