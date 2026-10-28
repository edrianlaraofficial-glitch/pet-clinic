<?php

$publicPath = __DIR__.'/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if (preg_match('#(?:^|/)\.(?!well-known(?:/|$))#', $uri)) {
    http_response_code(404);

    return true;
}

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
