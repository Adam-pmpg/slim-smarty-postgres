<?php

use Slim\App;
use Slim\Psr7\Request;  // Importowanie właściwej klasy Request
use Slim\Psr7\Response; // Jeśli potrzebujesz, chociaż to nie jest konieczne

function getHomepage(App $app, Smarty $smarty) {

    // Definiowanie akcji dla ścieżki "/"
    $app->get('/', function (Request $request, Response $response, $args) use ($smarty) {
        // Przypisanie zmiennej do szablonu Smarty
        $smarty->assign('message', 'Hello World');

        // Wyświetlenie szablonu
        $smarty->display('index.tpl');

        // Zwrócenie odpowiedzi
        return $response;
    });
};