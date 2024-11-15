<?php

// Funkcja definiująca trasę /users

//dla starej wersji Slim
//use Slim\Http\Request;
//use Slim\Http\Response;

//PSR-7: Slim 4.x używa standardu PSR-7 dla obiektów Request i Response
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function getUsers($app, $pdo) {
    $app->get('/users', function (Request $request, Response $response) use ($pdo) {
        // Zapytanie do bazy danych
        $stmt = $pdo->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Wysłanie odpowiedzi w formacie JSON
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    });
}