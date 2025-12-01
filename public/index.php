<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// さくらサーバー対応: __DIR__がシンボリックリンクで問題が発生する可能性があるため、
// シンボリックリンクを解決した実際のパスを使用
if (is_link(__FILE__)) {
    $basePath = dirname(realpath(__FILE__));
} else {
    $basePath = __DIR__;
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
$maintenance = $basePath.'/../storage/framework/maintenance.php';
if (file_exists($maintenance)) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
