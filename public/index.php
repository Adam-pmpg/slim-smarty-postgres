<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../db/db.php';
require __DIR__ . '/../src/routes/users.php';
require __DIR__ . '/../src/routes/uploadVideo.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

//Zmienna $app = new \Slim\App(); była stosowana w starszych wersjach Slim (do wersji 3.x)
// natomiast od Slim 4.x (i nowszych) framework Slim przeszedł na inny sposób inicjalizacji aplikacji przy użyciu AppFactory::create()
$app = AppFactory::create();

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

// Zarejestruj trasę /users
getUsers($app, $pdo);
// Zarejestruj trasę /upload-video
uploadVideo($app);

$app->run();
