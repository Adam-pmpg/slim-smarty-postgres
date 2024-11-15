<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

function uploadVideo($app) {
    // Zdefiniowanie trasy POST /upload-video
    $app->post('/upload-video', function (Request $request, Response $response) {
        // Sprawdzenie, czy plik został przesłany
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            // Ścieżka do folderu, w którym zapisujemy wideo
            $uploadDirectory = __DIR__ . '/uploads/';

            // Sprawdzamy, czy folder istnieje, jeśli nie, to go tworzymy
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            // Ścieżka, gdzie plik będzie zapisany
            $uploadedFilePath = $uploadDirectory . basename($_FILES['video']['name']);

            // Przeniesienie przesłanego pliku do docelowego folderu
            if (move_uploaded_file($_FILES['video']['tmp_name'], $uploadedFilePath)) {
                $responseData = [
                    'status' => 'success',
                    'message' => 'Video uploaded successfully!',
                    'file_path' => $uploadedFilePath
                ];
                return $response->withJson($responseData, 200);
            } else {
                // Błąd przy przesyłaniu pliku
                $responseData = [
                    'status' => 'error',
                    'message' => 'Failed to upload video.'
                ];
                return $response->withJson($responseData, 500);
            }
        } else {
            // Brak pliku lub błąd w przesyłaniu
            $responseData = [
                'status' => 'error',
                'message' => 'No video file uploaded or error during upload.'
            ];
            return $response->withJson($responseData, 400);
        }
    });
}
