<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

// 设置 PSR-17 工厂
$psr17Factory = new Psr17Factory();
AppFactory::setResponseFactory($psr17Factory);

// 创建 App 实例
$app = AppFactory::create();

// 创建 ServerRequest 实例
$creator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);
$request = $creator->fromGlobals();

// 定义路由
$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("Slim 4 API Ready!");
    return $response;
});

// 运行 App
$app->run($request);
