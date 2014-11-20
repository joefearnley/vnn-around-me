<?php

require 'vendor/autoload.php';
require 'config/database.php';

date_default_timezone_set('America/Detroit');

ActiveRecord\Config::initialize(function($config) use ($connections)
{
    $config->set_model_directory('models');
    $config->set_connections($connections);
    $config->set_default_connection('production');
});
