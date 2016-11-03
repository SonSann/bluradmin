<?php
// Application middleware

use \Slim\Middleware\HttpBasicAuthentication\PdoAuthenticator;

$container = $app->getContainer();

/*
$pdo = new \PDO('mysql:dbname=ccc;host=localhost', 'root', '');

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/api",
    "passthrough" => ["/api/auth/local"],
    "secure" => true,
    "relaxed" => ["localhost", "dev.example.com"],
    "realm" => "Protected",
    "users" => [
        "admin@example.com" => "admin",
        "test@example.com" => "test"
    ],
    "logger" => $container['logger'],
    "authenticator" => new PdoAuthenticator([
        "pdo" => $pdo,
        "table" => "accounts",
        "user" => "username",
        "hash" => "password"
    ]),
    "callback" => function ($request, $response, $arguments) use ($app) {
        print_r($arguments);
    },
    "error" => function ($request, $response, $arguments) {
        $data = [];
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response->write(json_encode($data, JSON_UNESCAPED_SLASHES));
    }
]));
*/

$app->add(new \Slim\Middleware\JwtAuthentication([
    "secret" => getenv("JWT_SECRET"),
    "path" => "/api",
    "passthrough" => ["/api/auth/local", "/admin/ping"],
    "cookie" => "token",
    "secure" => true,
    "relaxed" => ["localhost", "dev.example.com"],
    "realm" => "Protected",
    "logger" => $container['logger'],
    "users" => [
        "admin@example.com" => "admin",
        "test@example.com" => "test"
    ],
    "callback" => function ($request, $response, $arguments) use ($container) {
        $container["jwt"] = $arguments["decoded"];
    },
    "error" => function ($request, $response, $arguments) {
        $data = [];
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));
