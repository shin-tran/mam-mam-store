<?php
define('_HOST_URL', !!$_ENV['PRODUCTION'] ? $_ENV['HOST_URL'] : 'http://'.$_SERVER['HTTP_HOST'].$_ENV['BASE_PROJECT_NAME']);
define('_HOST_URL_PUBLIC', _HOST_URL.'/public');
define('_HOST_URL_DASHBOARD', _HOST_URL.'/dashboard');

// path setup
define('_PROJECT_ROOT', __DIR__.'/../..');
define('_PATH_URL_APP', _PROJECT_ROOT.'/app');
define('_PATH_URL_CONTROLLERS', _PROJECT_ROOT.'/app/Controllers');
define('_PATH_URL_CORE', _PROJECT_ROOT.'/app/Core');
define('_PATH_URL_VIEWS', _PROJECT_ROOT.'/app/Views');