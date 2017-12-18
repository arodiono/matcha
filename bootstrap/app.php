<?php

use Respect\Validation\Validator as v;
use Slim\Http\Request;
use Slim\Http\Response;

session_start();

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'XmmD3RKE',
            'database' => 'matcha',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci'
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
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => false,
        'debug' => true
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));
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
$container['PasswordController'] = function ($container) {
    return new \App\Controllers\PasswordController($container);
};
$container['PhotoController'] = function ($container) {
    return new \App\Controllers\PhotoController($container);
};
$container['UserController'] = function ($container) {
    return new \App\Controllers\UserController($container);
};
$container['csrf'] = function () {
    return new \Slim\Csrf\Guard;
};

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));
$app->add($container->get('csrf'));
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

v::with('App\\Validation\\Rules\\');


require __DIR__ . '/../app/routes.php';