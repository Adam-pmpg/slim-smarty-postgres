<?php
// Funkcja do rejestracji trasy scalania plików w Slim
function mergeVideo($app) {
    // Endpoint do scalania plików
    $app->post('/merge-video', function ($request, $response) {
        $chunksDir = __DIR__ . '/../../chunks/';
        $outputDir = __DIR__ . '/../../output/';

        // Pobierz listę plików w katalogu chunks (np. chunk_0__v-bdda1d43-f307-440a-8273-b696c916f976_original.mp4)
        $chunks = glob($chunksDir . 'chunk_*');
        if (empty($chunks)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Brak plików do scalania.'
            ], JSON_UNESCAPED_UNICODE));

            return $response->withStatus(404)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        // Podziel nazwę pierwszego fragmentu na części
        $firstChunk = basename($chunks[0]); // np. "chunk_0__v-bdda1d43-f307-440a-8273-b696c916f976_original.mp4"
        $parts = explode('__', $firstChunk); // Rozdzielenie na części, używając "__" jako separatora
        //error_log('Zawartość $parts: ' . json_encode($parts));
        if (count($parts) === 0) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Nie udało się wyodrębnić nazwy oryginalnego pliku.'
            ], JSON_UNESCAPED_UNICODE));

            return $response->withStatus(500)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        // Wydobycie oryginalnej nazwy z drugiej części (np. "v-bdda1d43-f307-440a-8273-b696c916f976_original.mp4")
        $originalNameWithExtension = $parts[1];  // Pozostaje pełna nazwa z rozszerzeniem

        // Zamiast wyodrębniać rozszerzenie, bezpośrednio tworzymy plik wynikowy z pełną nazwą
        $outputFile = $outputDir . $originalNameWithExtension;  // Pełna nazwa (z rozszerzeniem)

        // Tworzenie pliku wynikowego
        $outputHandle = fopen($outputFile, 'wb');
        if (!$outputHandle) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Nie udało się utworzyć pliku wynikowego.'
            ], JSON_UNESCAPED_UNICODE));

            return $response->withStatus(500)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

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
            'output_file' => $originalNameWithExtension
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    });
}
