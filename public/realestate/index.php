<?php

$bootstrap = __DIR__ . '/../index.php';
if (!file_exists($bootstrap)) {
    http_response_code(500);
    exit('Bootstrap file not found.');
}

$_SERVER['SCRIPT_FILENAME'] = $bootstrap;
if (!empty($_SERVER['SCRIPT_NAME'])) {
    $_SERVER['SCRIPT_NAME'] = preg_replace('#/realestate#', '', $_SERVER['SCRIPT_NAME'], 1);
}
if (!empty($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF'] = preg_replace('#/realestate#', '', $_SERVER['PHP_SELF'], 1);
}
chdir(dirname($bootstrap));

require $bootstrap;

