<?php

//require 'vendor/autoload.php';

//use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

//$app = AppFactory::create();

// Ruta para recibir parámetros id y fecha
$app->post('/procesar', function (Request $request, Response $response) {
    // Obtener los parámetros id y fecha del cuerpo de la solicitud
    $params = (array)$request->getParsedBody();
    //var_dump( $params );
    $id = $params['id'] ?? null;
    $fecha = $params['fecha'] ?? null;

    // Verificar que los parámetros sean válidos
    /*if (!$id || !$fecha) {
        $response->getBody()->write(json_encode(['error' => 'Parámetros inválidos.']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(400);
    }*/

    // Responder al cliente indicando que la petición se procesará
    $response->getBody()->write(json_encode(['status' => 'Petición recibida, se procesará en breve.']));
    $response = $response->withHeader('Content-Type', 'application/json')
                         ->withStatus(202);

    // Ejecutar la lógica de forma asíncrona
    $promise = new \GuzzleHttp\Promise\Promise(function () use (&$promise, $id, $fecha) {
        // Aquí se colocan las operaciones a la base de datos o cualquier otra lógica que desees ejecutar de forma asíncrona
        procesarDatos($id, $fecha);
        $promise->resolve('Procesamiento completado.');
    });

    // Iniciar la ejecución asíncrona
    $promise->then(function ($result) {
        // Este bloque se ejecuta cuando la promesa es resuelta
        error_log($result); // Puedes loggear o hacer algo más cuando termine el procesamiento
    });

    // Ejecutar la promesa asíncronamente
    $promise->wait(false);

    return $response;
});

// Función para simular la ejecución de scripts a BD
function procesarDatos($id, $fecha) {
    // Aquí colocas tu lógica para trabajar con la base de datos
    // Por ejemplo, ejecutar una consulta SQL, actualizar registros, etc.
    sleep(5); // Simular una operación que toma tiempo
    error_log("Procesado: ID $id, Fecha $fecha"); // Log para verificar la ejecución
}

//$app->run();
