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

function number_to_bangla_words(int $number): string
{
    $map = [
        0 => 'শূন্য', 1 => 'এক', 2 => 'দুই', 3 => 'তিন', 4 => 'চার', 5 => 'পাঁচ',
        6 => 'ছয়', 7 => 'সাত', 8 => 'আট', 9 => 'নয়', 10 => 'দশ', 11 => 'এগারো',
        12 => 'বারো', 13 => 'তেরো', 14 => 'চৌদ্দ', 15 => 'পনেরো', 16 => 'ষোল',
        17 => 'সতেরো', 18 => 'আঠারো', 19 => 'উনিশ', 20 => 'বিশ', 21 => 'একুশ',
        22 => 'বাইশ', 23 => 'তেইশ', 24 => 'চব্বিশ', 25 => 'পঁচিশ', 26 => 'ছাব্বিশ',
        27 => 'সাতাশ', 28 => 'আটাশ', 29 => 'ঊনত্রিশ', 30 => 'ত্রিশ', 31 => 'একত্রিশ',
        32 => 'বত্রিশ', 33 => 'তেত্রিশ', 34 => 'চৌত্রিশ', 35 => 'পঁয়ত্রিশ', 36 => 'ছত্রিশ',
        37 => 'সাঁইত্রিশ', 38 => 'আটত্রিশ', 39 => 'ঊনচল্লিশ', 40 => 'চল্লিশ', 41 => 'একচল্লিশ',
        42 => 'বিয়াল্লিশ', 43 => 'তেতাল্লিশ', 44 => 'চুয়াল্লিশ', 45 => 'পঁয়তাল্লিশ', 46 => 'ছেচল্লিশ',
        47 => 'সাতচল্লিশ', 48 => 'আটচল্লিশ', 49 => 'ঊনপঞ্চাশ', 50 => 'পঞ্চাশ', 51 => 'একান্ন',
        52 => 'বায়ান্ন', 53 => 'তিপ্পান্ন', 54 => 'চুয়ান্ন', 55 => 'পঁচান্ন', 56 => 'ছাপ্পান্ন',
        57 => 'সাতান্ন', 58 => 'আটান্ন', 59 => 'ঊনষাট', 60 => 'ষাট', 61 => 'একষট্টি',
        62 => 'বাষট্টি', 63 => 'তেষট্টি', 64 => 'চৌষট্টি', 65 => 'পঁয়ষট্টি', 66 => 'ছেষট্টি',
        67 => 'সাতষট্টি', 68 => 'আটষট্টি', 69 => 'ঊনসত্তর', 70 => 'সত্তর', 71 => 'একাত্তর',
        72 => 'বাহাত্তর', 73 => 'তিয়াত্তর', 74 => 'চুয়াত্তর', 75 => 'পঁচাত্তর', 76 => 'ছিয়াত্তর',
        77 => 'সাতাত্তর', 78 => 'আটাত্তর', 79 => 'ঊনআশি', 80 => 'আশি', 81 => 'একাশি',
        82 => 'বিরাশি', 83 => 'তিরাশি', 84 => 'চুরাশি', 85 => 'পঁচাশি', 86 => 'ছিয়াশি',
        87 => 'সাতাশি', 88 => 'আটাশি', 89 => 'ঊননব্বই', 90 => 'নব্বই', 91 => 'একানব্বই',
        92 => 'বিরানব্বই', 93 => 'তিরানব্বই', 94 => 'চুরানব্বই', 95 => 'পঁচানব্বই', 96 => 'ছিয়ানব্বই',
        97 => 'সাতানব্বই', 98 => 'আটানব্বই', 99 => 'নিরানব্বই',
    ];

    if ($number < 100) {
        return $map[$number] ?? (string) $number;
    }

    $parts = [];
    $crore = intdiv($number, 10000000);
    if ($crore > 0) {
        $parts[] = number_to_bangla_words($crore) . ' কোটি';
        $number %= 10000000;
    }

    $lakh = intdiv($number, 100000);
    if ($lakh > 0) {
        $parts[] = number_to_bangla_words($lakh) . ' লাখ';
        $number %= 100000;
    }

    $thousand = intdiv($number, 1000);
    if ($thousand > 0) {
        $parts[] = number_to_bangla_words($thousand) . ' হাজার';
        $number %= 1000;
    }

    $hundred = intdiv($number, 100);
    if ($hundred > 0) {
        $parts[] = number_to_bangla_words($hundred) . ' শত';
        $number %= 100;
    }

    if ($number > 0) {
        $parts[] = number_to_bangla_words($number);
    }

    return implode(' ', $parts);
}

function format_bangla_amount_in_words(float $amount): string
{
    $taka = (int) round($amount, 0);
    $words = number_to_bangla_words($taka);
    return $words . ' টাকা মাত্র';
}

function get_account_id_by_type_or_name(string $typeOrName): ?int
{
    [$pdo] = db_connection();
    if (!$pdo) {
        return null;
    }
    $normalized = strtolower(trim($typeOrName));
    $stmt = $pdo->prepare('SELECT id FROM accounts WHERE is_active = 1 AND (LOWER(type) = :value OR LOWER(name) = :value) ORDER BY id ASC LIMIT 1');
    $stmt->execute([':value' => $normalized]);
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

function ensure_transaction_category(string $name, string $type): ?int
{
    [$pdo] = db_connection();
    if (!$pdo) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id FROM transaction_categories WHERE name = :name AND type = :type LIMIT 1');
    $stmt->execute([':name' => $name, ':type' => $type]);
    $row = $stmt->fetch();
    if ($row) {
        return (int) $row['id'];
    }
    $insert = $pdo->prepare('INSERT INTO transaction_categories (name, type) VALUES (:name, :type)');
    $insert->execute([':name' => $name, ':type' => $type]);
    return (int) $pdo->lastInsertId();
}

function create_transaction(string $type, int $categoryId, int $accountId, float $amount, string $date, string $note = ''): void
{
    [$pdo] = db_connection();
    if (!$pdo) {
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO transactions (type, category_id, account_id, amount, txn_date, note) VALUES (:type, :category_id, :account_id, :amount, :txn_date, :note)');
    $stmt->execute([
        ':type' => $type,
        ':category_id' => $categoryId,
        ':account_id' => $accountId,
        ':amount' => $amount,
        ':txn_date' => $date,
        ':note' => $note,
    ]);
}
