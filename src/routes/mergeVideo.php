<?php

use function Utils\sortChunksByIndexInFileName;

/**
 * Funkcja do rejestracji trasy scalania plików w Slim
 *
 * @param $app
 * @return void
 */
function mergeVideo($app) {
    // Endpoint do scalania plików dla domyślnego katalogu
    $app->post('/merge-video', function ($request, $response) {
        $chunksDir = __DIR__ . '/../../chunks/';
        $outputDir = __DIR__ . '/../../output/';

        // Pobierz listę plików w katalogu chunks
        $chunks = glob($chunksDir . 'chunk_*');
        if (empty($chunks)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Brak plików do scalania.'
            ], JSON_UNESCAPED_UNICODE));

            return $response->withStatus(404)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        return processChunks($chunks, $outputDir, $response);
    });

    // Endpoint do scalania plików dla określonego katalogu
    $app->post('/merge-video/{folder_name}', function ($request, $response, $args) {
        $folderName = $args['folder_name'];
        $chunksDir = __DIR__ . '/../../chunks/' . $folderName . '/';
        $outputDir = __DIR__ . '/../../output/' . $folderName . '/';

        // Sprawdź, czy katalog wejściony z chunks, istnieje
        if (!is_dir($chunksDir)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Podany katalog nie istnieje: ' . $folderName
            ], JSON_UNESCAPED_UNICODE));

            return $response->withStatus(404)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
        // Pobierz listę plików w podanym katalogu
        $chunks = glob($chunksDir . 'chunk_*');
        if (empty($chunks)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Brak plików do scalania w katalogu: ' . $folderName
            ], JSON_UNESCAPED_UNICODE));

            return $response->withStatus(404)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        return processChunks($chunks, $outputDir, $response);
    });
}

// Funkcja pomocnicza do scalania plików
function processChunks($chunks, $outputDir, $response) {
    // Podziel nazwę pierwszego fragmentu na części
    $firstChunk = basename($chunks[0]); // np. "chunk_0__v-bdda1d43-f307-440a-8273-b696c916f976_original.mp4"
    $parts = explode('__', $firstChunk);
    if (count($parts) === 0) {
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Nie udało się wyodrębnić nazwy oryginalnego pliku.'
        ], JSON_UNESCAPED_UNICODE));

        return $response->withStatus(500)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    // Wydobycie oryginalnej nazwy z drugiej części
    $originalNameWithExtension = $parts[1];

    // Zamiast wyodrębniać rozszerzenie, bezpośrednio tworzymy plik wynikowy z pełną nazwą
    $outputFile = $outputDir . $originalNameWithExtension;
    // Tworzenie pliku wynikowego
    $outputHandle = fopen($outputFile, 'wb');
    if (!$outputHandle) {
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Nie udało się utworzyć pliku wynikowego.'
        ], JSON_UNESCAPED_UNICODE));

        return $response->withStatus(500)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
    $chunks = sortChunksByIndexInFileName($chunks);
    // Scalenie plików
    foreach ($chunks as $chunk) {
        $chunkHandle = fopen($chunk, 'rb');
        if (!$chunkHandle) {
            fclose($outputHandle);
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Nie udało się otworzyć części pliku: ' . basename($chunk)
            ], JSON_UNESCAPED_UNICODE));

            return $response->withStatus(500)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        // Zapisz zawartość kawałka do pliku wynikowego
        while (!feof($chunkHandle)) {
            $data = fread($chunkHandle, 8192); // Odczytuj kawałki o wielkości 8KB
            fwrite($outputHandle, $data);
        }

        fclose($chunkHandle);
    }

    fclose($outputHandle);

    $response->getBody()->write(json_encode([
        'status' => 'success',
        'message' => 'Plik został scalony pomyślnie.',
        'output_file' => basename($outputFile)
    ], JSON_UNESCAPED_UNICODE));

    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
}
