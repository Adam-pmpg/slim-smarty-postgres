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
            $response->getBody()->write("Brak plików do scalania.");
            return $response->withStatus(404);
        }

        // Podziel nazwę pierwszego fragmentu na części
        $firstChunk = basename($chunks[0]); // np. "chunk_0__v-bdda1d43-f307-440a-8273-b696c916f976_original.mp4"
        $parts = explode('__', $firstChunk); // Rozdziel na części, używając "__" jako separatora
        if (count($parts) < 2) {
            $response->getBody()->write("Nie udało się wyodrębnić nazwy oryginalnego pliku.");
            return $response->withStatus(500);
        }

        // Wydobycie oryginalnej nazwy z drugiej części (np. "v-bdda1d43-f307-440a-8273-b696c916f976_original.mp4")
        $originalNameWithExtension = $parts[1];
        $originalName = pathinfo($originalNameWithExtension, PATHINFO_FILENAME); // Usuwamy rozszerzenie

        // Wydobycie rozszerzenia z pierwszego pliku (np. .mp4, .wmv itd.)
        $fileExtension = pathinfo($chunks[0], PATHINFO_EXTENSION);
        $outputFile = $outputDir . $originalName . '.' . $fileExtension;

        // Tworzenie pliku wynikowego
        $outputHandle = fopen($outputFile, 'wb');
        if (!$outputHandle) {
            $response->getBody()->write("Nie udało się utworzyć pliku wynikowego.");
            return $response->withStatus(500);
        }

        // Scalenie plików
        foreach ($chunks as $chunk) {
            $chunkHandle = fopen($chunk, 'rb');
            if (!$chunkHandle) {
                fclose($outputHandle);
                $response->getBody()->write("Nie udało się otworzyć części pliku: " . basename($chunk));
                return $response->withStatus(500);
            }

            // Zapisz zawartość kawałka do pliku wynikowego
            while (!feof($chunkHandle)) {
                $data = fread($chunkHandle, 8192); // Odczytuj kawałki o wielkości 8KB
                fwrite($outputHandle, $data);
            }

            fclose($chunkHandle);
        }

        fclose($outputHandle);

        // Zwrocenie odpowiedzi z sukcesem
        $response->getBody()->write("Plik został scalony pomyślnie.");
        return $response;
    });
}
