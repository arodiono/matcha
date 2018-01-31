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
    $this->get('/password/forgot', 'PasswordController:getForgotPassword')->setName('password.forgot');
    $this->post('/password/forgot', 'PasswordController:postForgotPassword');
    $this->get('/password/reset/{hash}', 'PasswordController:getResetPassword')->setName('password.reset');
    $this->post('/password/reset/{hash}', 'PasswordController:postResetPassword');
})->add(new \App\Middleware\GuestMiddleware($container));

$app->group('', function () {
    $this->get('/signout', 'AuthController:getSignOut')->setName('signout');

    $this->get('/password/change', 'PasswordController:getChangePassword')->setName('password.change');
    $this->post('/password/change', 'PasswordController:postChangePassword');

    $this->get('/signup/info', 'AuthController:getSignUpInfo')->setName('signup.info');
    $this->post('/signup/info', 'AuthController:postSignUpInfo');

    $this->get('/signup/photos', 'AuthController:getSignUpPhotos')->setName('signup.photos');
    $this->post('/signup/photos', 'AuthController:postSignUpPhotos');

    $this->post('/photo/upload', 'PhotoController:upload')->setName('photo.upload');
    $this->post('/photo/set', 'PhotoController:setProfilePhoto')->setName('photo.set');
	
	$this->get('/user/edit', 'UserController:getUserEdit')->setName('user.edit');
	$this->post('/user/edit', 'UserController:postUserEdit');
	
    $this->post('/user/location', 'UserController:setLocation')->setName('user.location');
    $this->get('/user/{name}', 'UserController:getUserProfile')->setName('user.profile');
	
	$this->get('/search/nearby', 'SearchController:getNearbyUsers')->setName('search.nearby');
	
})->add(new \App\Middleware\AuthMiddleware($container));