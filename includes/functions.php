<?php
require_once __DIR__ . '/../db.php';

function db_connection(): array
{
    global $pdo, $db_error;
    return [$pdo, $db_error ?? null];
}

function fetch_all(string $sql, array $params = []): array
{
    [$pdo] = db_connection();
    if (!$pdo) {
        return [];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_one(string $sql, array $params = []): ?array
{
    [$pdo] = db_connection();
    if (!$pdo) {
        return null;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ?: null;
}

function format_currency(?string $currency, $amount): string
{
    $symbol = $currency ?: '৳';
    $value = number_format((float) $amount, 2);
    return $symbol . ' ' . $value;
}
