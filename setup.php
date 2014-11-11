<?php

require 'vendor/autoload.php';

date_default_timezone_set('America/Detroit');

$connections = [
	'production' => 'mysql://vnn:password@localhost/vnnaroundme'
];

ActiveRecord\Config::initialize(function($config) use ($connections)
{
	$config->set_model_directory('models');
	$config->set_connections($connections);
	$config->set_default_connection('production');
});
