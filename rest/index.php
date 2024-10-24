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

require __DIR__  . '/endpoints/insertarClienteFacturacion.php';
require __DIR__  . '/endpoints/insertaVentaFacturacion.php';

require __DIR__  . '/endpoints/buscaVentasPorFolio.php';
require __DIR__  . '/endpoints/buscaClientesPorRfc.php';

require __DIR__  . '/endpoints/actualizaSubtipoPago.php';
require __DIR__  . '/endpoints/insertaVentaSistemaFacturacion.php';
require __DIR__  . '/endpoints/enviaFacturaCorreo.php';
require __DIR__  . '/endpoints/inserta_cliente.php';
require __DIR__  . '/endpoints/envia_cliente_facturacion.php';
require __DIR__  . '/endpoints/descarga_clientes.php';
//para ejecutar consultas en el servidor
require __DIR__  . '/endpoints/ejecuta_consulta_en_servidor.php';


$app->run();
