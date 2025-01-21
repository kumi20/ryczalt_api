<?php

require_once '../vendor/autoload.php';

use Slim\Factory\AppFactory;
use src\controllers\CustomerController;

$app = AppFactory::create();

// Trasa GET /customers
$app->get('/api_ryczalt/customers', [CustomerController::class, 'getCustomers']);

$app->get('/api_ryczalt/login', [CustomerController::class, 'getCustomers']);

// Trasa do wyświetlenia dokumentacji w Redoc
$app->get('/docs', function ($request, $response, $args) {
    $html = <<<HTML
<!DOCTYPE html>
<html>
  <head>
    <title>API Documentation</title>
    <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
  </head>
  <body>
    <redoc spec-url='openapi.yaml'></redoc>
  </body>
</html>
HTML;

    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

$app->run();
