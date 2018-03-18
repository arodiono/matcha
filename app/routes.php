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

    $this->group('/user', function () {
        $this->get('/password/forgot', 'UserController:getForgotPassword')->setName('user.password.forgot');
        $this->post('/password/forgot', 'UserController:postForgotPassword');
        $this->get('/password/reset/{hash}', 'UserController:getResetPassword')->setName('user.password.reset');
        $this->post('/password/reset/{hash}', 'UserController:postResetPassword');
    });
})->add(new \App\Middleware\GuestMiddleware($container));

$app->group('', function () {

    $this->get('/signout', 'AuthController:getSignOut')->setName('signout');

    $this->group('/user', function () {
        $this->get('/edit', 'UserController:getUserEdit')->setName('user.edit');
        $this->post('/edit', 'UserController:postUserEdit');
        $this->any('/location', 'UserController:setLocation')->setName('user.location');
        $this->get('/{name}', 'UserController:getUserProfile')->setName('user.profile');
        $this->get('/password/change', 'UserController:getChangePassword')->setName('user.password.change');
        $this->post('/password/change', 'UserController:postChangePassword');
    });

    $this->group('/signup', function () {
        $this->get('/info', 'AuthController:getSignUpInfo')->setName('signup.info');
        $this->post('/info', 'AuthController:postSignUpInfo');
        $this->get('/photos', 'AuthController:getSignUpPhotos')->setName('signup.photos');
        $this->post('/photos', 'AuthController:postSignUpPhotos');
    });

    $this->group('/photo', function () {
        $this->post('/upload', 'PhotoController:upload')->setName('photo.upload');
        $this->post('/set', 'PhotoController:setProfilePhoto')->setName('photo.set');
    });

	$this->group('/search', function () {
        $this->get('/nearby', 'SearchController:getNearbyUsers')->setName('search.nearby');
        $this->get('/tag/{id}', 'SearchController:getUsersByTag')->setName('search.tag');
    });

})->add(new \App\Middleware\AuthMiddleware($container));

$app->group('', function () {
    $this->group('/messages', function() {
        $this->get('/{name}', 'MessageController:getMessage')->setName('messages');
        $this->post('/{name}', 'MessageController:postMessage');
    });

})->add(new \App\Middleware\MessageMiddleware($container));