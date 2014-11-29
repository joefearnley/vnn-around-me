<?php

require 'vendor/autoload.php';
require 'config/database.php';

date_default_timezone_set('America/Detroit');

ActiveRecord\Config::initialize(function($config) use ($connections)
{
    $config->set_model_directory('models');
    $config->set_connections($connections);

    $defaultConnection = null;
    $environment = $_SERVER['HTTP_HOST'];
    if (strpos($environment,'localhost') !== false) {
    	$defaultConnection = 'test';
	} else {
		$defaultConnection = 'production';
	}

    $config->set_default_connection($defaultConnection);
});
