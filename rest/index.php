<?php
require __DIR__ . '/vendor/autoload.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->setBasePath('/adminFacturacion/rest');

$app->get('/example', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->get('/example_2', function (Request $request, Response $response, $args) {
    //$response->getBody()->write("Hello world 2!");
   // return $response;
    $data = array( "test"=>"1" );
    $response->getBody()->write(json_encode($data));
    //$response = $response->withJson($data);
    return $response;
    return json_encode( array( "test"=>"1" ) );
});

$app->run();
