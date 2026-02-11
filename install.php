<?php
/**
 * Установка БД: создание базы, таблиц и начальных данных.
 * Запустите один раз: http://localhost/po_alina/install.php
 * После установки удалите или переименуйте этот файл.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'po_alina_warehouse';

echo "<h1>Установка базы данных</h1>";

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
    echo "<p>База данных создана или уже существует.</p>";

    $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
    $schema = preg_replace('/CREATE DATABASE.*?;/s', '', $schema);
    $schema = preg_replace('/USE po_alina_warehouse;/', '', $schema);
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $sql) {
        if ($sql !== '' && !preg_match('/^--/', $sql)) {
            $pdo->exec($sql);
        }
    }
    echo "<p>Таблицы созданы.</p>";

    $seed = file_get_contents(__DIR__ . '/sql/seed.sql');
    $seed = preg_replace('/USE po_alina_warehouse;/', '', $seed);
    $lines = explode("\n", $seed);
    $current = '';
    foreach ($lines as $line) {
        $line = trim($line);
        if (substr($line, 0, 2) === '--') continue;
        $current .= $line . "\n";
        if (substr(rtrim($line), -1) === ';') {
            $pdo->exec($current);
            $current = '';
        }
    }
    if (trim($current) !== '') {
        $pdo->exec($current);
    }
    echo "<p>Начальные данные загружены.</p>";

    echo "<p><strong>Готово.</strong> <a href=\"/po_alina/login.php\">Перейти к входу</a></p>";
} catch (PDOException $e) {
    echo "<p style=\"color:red;\">Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
}
