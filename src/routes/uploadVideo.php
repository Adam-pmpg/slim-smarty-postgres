<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

function uploadVideo($app) {
    $app->post('/upload', function (Request $request, Response $response) {
        // Odczytujemy dane z formularza
        $parsedBody = $request->getParsedBody();
        $chunkIndex = isset($parsedBody['chunkIndex']) ? (int)$parsedBody['chunkIndex'] : null;
        $totalChunks = isset($parsedBody['totalChunks']) ? (int)$parsedBody['totalChunks'] : null;

        // Sprawdzamy, czy chunkIndex i totalChunks są przesłane
        if ($chunkIndex === null || $totalChunks === null) {
            $responseData = [
                'status' => 'error',
                'message' => 'Missing chunkIndex or totalChunks parameter.',
                'chunkIndex' => $chunkIndex
            ];
            $response->getBody()->write(json_encode($responseData));  // Zapisujemy dane w ciele odpowiedzi
            return $response
                ->withHeader('Content-Type', 'application/json')  // Ustawiamy nagłówek Content-Type
                ->withStatus(400); // Kod odpowiedzi HTTP (400 - Bad Request)
        }

        // Sprawdzamy, czy plik został przesłany
        $uploadedFiles = $request->getUploadedFiles();
        if (!isset($uploadedFiles['file'])) {
            $responseData = [
                'status' => 'error',
                'message' => 'No file uploaded.'
            ];
            $response->getBody()->write(json_encode($responseData));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $file = $uploadedFiles['file'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $responseData = [
                'status' => 'error',
                'message' => 'Error uploading file.'
            ];
            $response->getBody()->write(json_encode($responseData));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }

        $originalName = $file->getClientFilename();
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $chunksDir = __DIR__ . "/../../chunks/{$safeName}";

        if (!is_dir($chunksDir)) {
            mkdir($chunksDir, 0777, true);
        }

        //$chunkIndex w formacie trzycyfrowym, później do sortowania
        $chunkIndexFormatted = str_pad($chunkIndex, 3, "0", STR_PAD_LEFT);
        // Ścieżka do zapisu fragmentu
        $chunkPath = $chunksDir . "/chunk_{$chunkIndexFormatted}__{$originalName}";

        /*var_dump([
            '$chunkPath' => $chunkPath,
            '$originalName' => $originalName,
        ]);*/

        // Zapisujemy fragment na dysku
        $file->moveTo($chunkPath);

        // Logowanie, ale to echo robi błąd w responsie dla CMSa
        //echo "Fragment $chunkIndex zapisany: $chunkPath\n";
        error_log("Fragment $chunkIndex zapisany: $chunkPath");

        // Zwracamy odpowiedź o powodzeniu z danymi o postępie
        $responseData = [
            'status' => 'success',
            'message' => 'Chunk uploaded successfully.',
            'chunkIndex' => $chunkIndex,
            'totalChunks' => $totalChunks,
            'progress' => ($chunkIndex + 1) / $totalChunks * 100, // Procent ukończenia
            'file' => $originalName
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200); // Kod odpowiedzi HTTP (200 - OK)
    });
}
