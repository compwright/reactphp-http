<?php

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Io\MiddlewareRunner;
use React\Http\Message\Response;
use React\Http\Message\ServerRequest;

const ITERATIONS = 5000;
const MIDDLEWARE_COUNT = 512;

require __DIR__ . '/../vendor/autoload.php';

$middleware = function (ServerRequestInterface $request, $next) {
    return $next($request);
};
$middlewareList = [];
for ($i = 0; $i < MIDDLEWARE_COUNT; $i++) {
    $middlewareList[] = $middleware;
}
$middlewareList[] = function (ServerRequestInterface $request) {
    return new Response(545);
};
$middlewareRunner = new MiddlewareRunner($middlewareList);
$request = new ServerRequest('GET', 'https://example.com/');

$start = microtime(true);
for ($i = 0; $i < ITERATIONS; $i++) {
    $middlewareRunner($request);
}
$time = microtime(true) - $start;

echo 'Ran a request ', number_format(ITERATIONS), ' times through the middleware runner (with ', MIDDLEWARE_COUNT, ' middleware) in: ', number_format($time, 2), 's', PHP_EOL;
