<?php

// Funkcja pomocnicza do sortowania plików fragmentów
function sortChunksByIndexInFileName(array $chunks) {
    usort($chunks, function($a, $b) {
        // Wyciągamy numer z nazwy pliku
        preg_match('/chunk_(\d+)_/', basename($a), $matchA);
        preg_match('/chunk_(\d+)_/', basename($b), $matchB);
        $numA = isset($matchA[1]) ? (int)$matchA[1] : 0;
        $numB = isset($matchB[1]) ? (int)$matchB[1] : 0;
        return $numA - $numB;
    });
    return $chunks;
}