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
    $this->get('/fb-login', 'AuthController:facebookCallback')->setName('fb-login');
    $this->get('/intra-login', 'AuthController:intraSignUp')->setName('intra-login');


    $this->group('/user', function () {
        $this->post('/online', 'UserController:postOnline');
        $this->get('/password/forgot', 'UserController:getForgotPassword')->setName('user.password.forgot');
        $this->post('/password/forgot', 'UserController:postForgotPassword');
        $this->get('/password/reset/{hash}', 'UserController:getResetPassword')->setName('user.password.reset');
        $this->post('/password/reset/{hash}', 'UserController:postResetPassword');
    });
})->add(new \App\Middleware\GuestMiddleware($container));

$app->group('', function () {

    $this->get('/signout', 'AuthController:getSignOut')->setName('signout');

//    $this->group('/ws', function () {
//    });

    $this->group('/user', function () {
        $this->get('/edit', 'UserController:getUserEdit')->setName('user.edit');
        $this->post('/edit', 'UserController:postUserEdit');
        $this->any('/location', 'UserController:setLocation')->setName('user.location');
        $this->get('/password/change', 'UserController:getChangePassword')->setName('user.password.change');
        $this->post('/password/change', 'UserController:postChangePassword');
        $this->post('/delete', 'UserController:postDeleteUser')->setName('user.delete');
        $this->get('/{name}/block', 'BlockController:blockUser')->setName('user.block');
        $this->get('/{name}/report', 'FakeController:setFakeUser')->setName('user.report');
        $this->get('/notifications', 'NotificationController:getNotifications')->setName('user.notifications');
        $this->get('/{name}', 'UserController:getUserProfile')->setName('user.profile');
    });

    $this->group('/like', function () {
        $this->post('/toggle/{id}', 'LikeController:toggleLike')->setName('like.toggle');
    });

    $this->group('/signup', function () {
        $this->get('/info', 'AuthController:getSignUpInfo')->setName('signup.info');
        $this->post('/info', 'AuthController:postSignUpInfo');
        $this->get('/photos', 'AuthController:getSignUpPhotos')->setName('signup.photos');
//        $this->post('/photos', 'AuthController:postSignUpPhotos');
    });

    $this->group('/photo', function () {
        $this->post('/upload', 'PhotoController:upload')->setName('photo.upload');
        $this->post('/set', 'PhotoController:setProfilePhoto')->setName('photo.set');
        $this->post('/{id}/delete', 'PhotoController:deletePhoto')->setName('photo.delete');
    });

	$this->group('/search', function () {
        $this->get('', 'SearchController:advancedSearch')->setName('search.advanced');
        $this->get('/nearby', 'SearchController:getNearbyUsers')->setName('search.nearby');
        $this->get('/tag/{id}', 'SearchController:getUsersByTag')->setName('search.tag');
    });

    $this->group('/messages', function() {
        $this->get('', 'MessageController:getAllConversations')->setName('messages.all');
        $this->get('/{name}', 'MessageController:getMessages')->setName('messages.chat');
        $this->post('/{name}/smhbr', 'MessageController:setMessageHasBeenRead');
    });

    $this->group('/messages', function() {
        $this->post('/{name}', 'MessageController:postMessage');
    });

    $this->group('/connection', function() {
        $this->post('/get', 'ConnectionController:getConnection');
        $this->post('/set', 'ConnectionController:setConnection');
        $this->post('/delete', 'ConnectionController:deleteConnection');
    });

})->add(new \App\Middleware\AuthMiddleware($container));