<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ClienteController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/PaymentController.php';
require_once __DIR__ . '/controllers/SellController.php';

$db = Database::getConnection();

$controllers = [
    'auth'     => new AuthController($db),
    'clientes' => new ClienteController($db),
    'produtos' => new ProductController($db),
    'pagamentos' => new PaymentController($db),
    'vendas'   => new SellController($db),
];

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route  = str_replace('/project-vendas/api/index.php', '', $uri);

$routes = [

    'POST' => [
        '/login'      => [$controllers['auth'], 'login'],
        '/clientes'   => [$controllers['clientes'], 'store'],
        '/produtos'   => [$controllers['produtos'], 'store'],
        '/pagamentos' => [$controllers['pagamentos'], 'store'],
        '/vendas'     => [$controllers['vendas'], 'store'],
    ],

    'GET' => [
        '/clientes'   => isset($_GET['id_cliente'])
            ? [$controllers['clientes'], 'show']
            : [$controllers['clientes'], 'index'],

        '/produtos'   => isset($_GET['id_produto'])
            ? [$controllers['produtos'], 'show']
            : [$controllers['produtos'], 'index'],

        '/pagamentos' => isset($_GET['id_pagamento'])
            ? [$controllers['pagamentos'], 'show']
            : [$controllers['pagamentos'], 'index'],

        '/vendas'     => [$controllers['vendas'], 'show'],
    ],

    'PUT' => [
        '/clientes'   => [$controllers['clientes'], 'update'],
        '/produtos'   => [$controllers['produtos'], 'update'],
        '/pagamentos' => [$controllers['pagamentos'], 'update'],
    ],

    'DELETE' => [
        '/clientes'   => [$controllers['clientes'], 'delete'],
        '/produtos'   => [$controllers['produtos'], 'delete'],
        '/pagamentos' => [$controllers['pagamentos'], 'delete'],
        '/vendas'     => [$controllers['vendas'], 'delete'],
    ],
];

if (isset($routes[$method][$route])) {
    call_user_func($routes[$method][$route]);
    exit;
}

http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'Endpoint não encontrado',
    'debug' => [
        'route' => $route,
        'method' => $method
    ]
]);
