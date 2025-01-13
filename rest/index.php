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

/****************************************************Endpoints servidor****************************************************/
  //clientes
    require __DIR__  . '/endpoints/inserta_cliente.php';//sevidor para insertar cliente desde la pantalla de clientes en sistemas locales.
    require __DIR__  . '/endpoints/inserta_cliente_directo.php';//servidor para insertar clientes desde la pantalla de facturacion de clientes (ambiente público).
    require __DIR__  . '/endpoints/buscaClientesPorRfc.php';//API para buscar clientes por RFC.
    require __DIR__  . '/endpoints/descarga_clientes.php';//API para obtener clientes para descargar en los servidores locales.
  //ventas
    require __DIR__  . '/endpoints/buscaVentasPorFolio.php';
    require __DIR__  . '/endpoints/actualizaSubtipoPago.php';
    require __DIR__  . '/endpoints/insertaVentaSistemaFacturacion.php';
  //Ejecutar consultas en el servidor
    require __DIR__  . '/endpoints/ejecuta_consulta_en_servidor.php';

/*******************************************Endpoints cliente para consumir apis*******************************************/
  //clientes
    require __DIR__  . '/endpoints/envia_cliente_facturacion.php';
  //ventas
    require __DIR__  . '/endpoints/enviaFacturaCorreo.php';
   // require __DIR__  . '/endpoints/insertaVentaFacturacion.php';
    require __DIR__  . '/endpoints/insertaVentaFacturacionPorLote.php';
    require __DIR__  . '/endpoints/barrido_ventas_por_lote.php';

//solicitud de factura 
    require __DIR__ . '/endpoints/solicitud_factura.php';
//previo de barrido de ventas
    require __DIR__  . '/endpoints/barridoVentasPrevio.php';
/*Deshabilitados*/
    //require __DIR__  . '/endpoints/prueba.php';
    //require __DIR__  . '/endpoints/insertarClienteFacturacion.php';
    
$app->run();
