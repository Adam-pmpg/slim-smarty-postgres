<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Tuupola\Middleware\CorsMiddleware;
use Utils\DatabasePostgres;

$db = new DatabasePostgres('db-slim', 5432, 'my_database', 'user', 'password');
$pdo = $db->getConnection();

$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates');  // Katalog szablonów
$smarty->setCompileDir(__DIR__ . '/../templates_c'); // Katalog kompilacji
$smarty->setCacheDir(__DIR__ . '/../cache');

//Zmienna $app = new \Slim\App(); była stosowana w starszych wersjach Slim (do wersji 3.x)
// natomiast od Slim 4.x (i nowszych) framework Slim przeszedł na inny sposób inicjalizacji aplikacji przy użyciu AppFactory::create()
$app = AppFactory::create();

$app->add(new CorsMiddleware([
    "origin" => ["*"],  // Zezwala na dostęp z dowolnej domeny
    "methods" => ["GET", "POST", "PUT", "DELETE", "OPTIONS"],  // Dozwolone metody HTTP
    "headers.allow" => ["Content-Type", "Authorization", "X-Requested-With"],
    "headers.expose" => [],  // Nagłówki, które mogą być dostępne dla aplikacji JavaScript
    "credentials" => true,  // Włącza obsługę poświadczeń (np. cookies)
    "maxAge" => 3600,  // Czas życia odpowiedzi CORS w sekundach
]));

$app->options('/{routes:.+}', function ($request, $response) {
    return $response->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withStatus(200);
});

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

//homepage
getHomepage($app, $smarty);
// Zarejestruj trasę /users
getUsers($app, $pdo);
// Zarejestruj trasę /upload-video
uploadVideo($app);

clearChunks($app);

mergeVideo($app);

$app->run();
