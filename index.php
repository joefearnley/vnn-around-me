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

$app->get('/school/find', function () use ($app) {
	$latitude = $app->request->get('latitude');
	$longitude = $app->request->get('longitude');

	
});

$app->run();