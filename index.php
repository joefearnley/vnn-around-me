<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

ActiveRecord\Config::initialize(function($config) {
    $config->set_model_directory('models');
    $config->set_connections([
        'production' => 'mysql://vnn:password@localhost/vnnaroundme'
    ]);
});
