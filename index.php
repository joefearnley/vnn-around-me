<?php

require 'vendor/autoload.php';
require 'setup.php';

$app = new \Slim\Slim(
	[
		'debug' => true,
		'templates.path' => 'views'
	]
);

$app->get('/', function () use ($app) {
	$app->render('home.php');
});

$app->run();