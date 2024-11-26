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


        foreach ($directories as $baseDirectory) {
            if (!is_dir($baseDirectory)) {
                $errors[] = "Folder bazowy nie istnieje: $baseDirectory";
                continue;
            }

            // Pobierz listę podfolderów w katalogu głównym
            $subfolders = array_filter(glob($baseDirectory . '/*'), 'is_dir');

            foreach ($subfolders as $folder) {
                // Pobierz listę plików w podfolderze
                $files = glob("$folder/*");
                foreach ($files as $file) {
                    if (is_file($file)) {
                        if (!unlink($file)) {
                            $errors[] = "Nie udało się usunąć pliku: $file";
                        }
                    }
                }

                // Usuń pusty folder
                if (!rmdir($folder)) {
                    $errors[] = "Nie udało się usunąć folderu: $folder";
                }
            }
            // Pobierz listę plików w katalogu głównym i je usuń
            $filesInBaseDir = glob("$baseDirectory/*");
            foreach ($filesInBaseDir as $file) {
                if (is_file($file)) {
                    if (!unlink($file)) {
                        $errors[] = "Nie udało się usunąć pliku: $file";
                    }
                }
            }
        }

        if (!empty($errors)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Niektóre pliki lub foldery nie zostały usunięte',
                'details' => $errors
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Wszystkie pliki i foldery zostały usunięte'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });
}
