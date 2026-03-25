<?php

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');

if ($basePath !== '' && $basePath !== '/' && str_starts_with($uriPath, $basePath)) {
    $uriPath = substr($uriPath, strlen($basePath));
}

$route = '/' . ltrim($uriPath, '/');
$route = rtrim($route, '/');
$route = $route === '' ? '/' : $route;

$routes = [
    '/' => __DIR__ . '/index.php',
    '/admin/login' => __DIR__ . '/admin/login.php',
    '/admin/dashboard' => __DIR__ . '/admin/dashboard.php',
    '/check-status' => __DIR__ . '/check-status/check-status.php',
    '/submit-complaint' => __DIR__ . '/submit_complaint.php',
];

if (isset($routes[$route])) {
    require $routes[$route];
    exit;
}

http_response_code(404);
echo '404 Not Found';
