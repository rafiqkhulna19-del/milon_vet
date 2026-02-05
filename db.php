<?php
$settings = require __DIR__ . '/config.php';

$host = 'localhost';
$dbname = 'milon_vet';
$user = 'root';
$pass = '';

$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $exception) {
    $pdo = null;
    $db_error = $exception->getMessage();
}
