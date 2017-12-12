<?php

$app->get('/', 'HomeController:index')->setName('home');

$app->get('/terms', function ($request, $response) use ($container) {
    return $container->view->render($response, 'terms.twig');
})->setName('terms');

$app->group('', function () {
    $this->get('/signup', 'AuthController:getSignUp')->setName('signup');
    $this->post('/signup', 'AuthController:postSignUp');
    $this->get('/signin', 'AuthController:getSignIn')->setName('signin');
    $this->post('/signin', 'AuthController:postSignIn');
})->add(new \App\Middleware\GuestMiddleware($container));

$app->group('', function () {
    $this->get('/signout', 'AuthController:getSignOut')->setName('signout');
    $this->get('/password/change', 'PasswordController:getChangePassword')->setName('password.change');
    $this->post('/password/change', 'PasswordController:postChangePassword');
    $this->get('/signup/finish', 'AuthController:getSignUpFinish')->setName('signup.finish');
    $this->post('/signup/finish', 'AuthController:postSignUpFinish');
})->add(new \App\Middleware\AuthMiddleware($container));