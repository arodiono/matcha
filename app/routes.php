<?php

$app->get('/', 'HomeController:index')->setName('home');

$app->get('/signup', 'AuthController:getSignUp')->setName('signup');
$app->post('/signup', 'AuthController:postSignUp');

$app->get('/signin', 'AuthController:getSignIn')->setName('signin');
$app->post('/signin', 'AuthController:postSignIn');
