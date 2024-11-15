<?php

// Parametry połączenia z bazą danych
$dsn = "pgsql:host=db-slim;port=5432;dbname=my_database;user=user;password=password";

try {
    // Tworzenie połączenia z bazą danych
    $pdo = new PDO($dsn);
    // Ustawienia dla PDO: błędy będą zgłaszane jako wyjątki
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Możesz tu dodać inne ustawienia połączenia, jeśli potrzebujesz
} catch (PDOException $e) {
    // Jeśli połączenie nie powiedzie się, wyświetlimy komunikat o błędzie
    die("Connection failed: " . $e->getMessage());
}