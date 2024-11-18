<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function clearChunks($app) {
    $app->delete('/clear-chunks', function (Request $request, Response $response) {
        $directories = [
            __DIR__ . '/../../chunks',
            __DIR__ . '/../../output',
        ];

        $errors = [];
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $files = glob("$dir/*"); // Pobiera wszystkie pliki w folderze
                foreach ($files as $file) {
                    if (is_file($file)) {
                        if (!unlink($file)) {
                            $errors[] = "Nie udało się usunąć pliku: $file";
                        }
                    }
                }
            } else {
                $errors[] = "Folder nie istnieje: $dir";
            }
        }

        if (!empty($errors)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Niektóre pliki nie zostały usunięte',
                'details' => $errors
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Wszystkie pliki zostały usunięte'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });
}
