<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

function uploadVideo($app) {
    $app->post('/upload', function (Request $request, Response $response) {
        // Odczytujemy dane z formularza
        $parsedBody = $request->getParsedBody();
        $chunkIndex = isset($parsedBody['chunkIndex']) ? (int)$parsedBody['chunkIndex'] : null;
        $totalChunks = isset($parsedBody['totalChunks']) ? (int)$parsedBody['totalChunks'] : null;

        // Sprawdzamy czy chunkIndex lub totalChunks są przesłane
        if ($chunkIndex === null || $totalChunks === null) {
            $responseData = [
                'status' => 'error',
                'message' => 'Missing chunkIndex or totalChunks parameter.',
                'chunkIndex' => $chunkIndex
            ];

            // Zwracamy odpowiedź z błędem w formacie JSON
            $response->getBody()->write(json_encode($responseData));  // Zapisujemy dane w ciele odpowiedzi
            return $response
                ->withHeader('Content-Type', 'application/json')  // Ustawiamy nagłówek Content-Type
                ->withStatus(400); // Kod odpowiedzi HTTP (w tym przypadku 400)
        }

        // Tworzymy odpowiedź o powodzeniu
        $responseData = [
            'status' => 'success',
            'message' => 'Data received successfully!',
            'chunk_index' => $chunkIndex,
            'total_chunks' => $totalChunks
        ];

        // Zwracamy odpowiedź z powodzeniem w formacie JSON
        $response->getBody()->write(json_encode($responseData));  // Zapisujemy dane w ciele odpowiedzi
        return $response
            ->withHeader('Content-Type', 'application/json')  // Ustawiamy nagłówek Content-Type
            ->withStatus(200); // Kod odpowiedzi HTTP (w tym przypadku 200)
    });
}