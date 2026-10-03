<?php
// XAMPP default: MySQL user root with an empty password.
$host = 'localhost';
$db   = 'camera_rental_system';
$user = 'root';
$pass = '';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = null;

function initializeDatabase(PDO $setup, string $db): void {
    $setup->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $setup->exec("USE `$db`");

    $sql = file_get_contents(__DIR__ . '/database.sql');
    $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql, -1, PREG_SPLIT_NO_EMPTY);

    foreach ($statements as $statement) {
        $trimmed = trim($statement);
        if ($trimmed === '' || preg_match('/^\s*(--|#)/', $trimmed)) {
            continue;
        }
        $setup->exec($trimmed);
    }
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, $options);

    $requiredTables = ['users', 'products', 'rentals', 'rental_items'];
    $tableCount = (int)$pdo->query(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('users', 'products', 'rentals', 'rental_items')"
    )->fetchColumn();

    if ($tableCount < count($requiredTables)) {
        throw new PDOException('Required tables are missing.');
    }
} catch (PDOException $e) {
    try {
        $setup = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, $options);
        initializeDatabase($setup, $db);
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, $options);
    } catch (PDOException $e) {
        $pdo = null; // api.php reports this to the page
    }
}
