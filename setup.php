<?php

require 'vendor/autoload.php';

date_default_timezone_set('America/Detroit');

$connections = [
	'production' => 'mysql://vnn:password@localhost/vnnaroundme'
];

ActiveRecord\Config::initialize(function($cfg) use ($connections)
{
	$cfg->set_model_directory('../models');
	$cfg->set_connections($connections);
	$cfg->set_default_connection('production');
});
