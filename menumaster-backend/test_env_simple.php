<?php
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo 'DB_NAME: ' . ($_ENV['DB_NAME'] ?? 'NOT SET') . PHP_EOL;
echo 'DB_HOST: ' . ($_ENV['DB_HOST'] ?? 'NOT SET') . PHP_EOL;
echo 'DB_USER: ' . ($_ENV['DB_USER'] ?? 'NOT SET') . PHP_EOL;
echo 'DB_PASS: ' . ($_ENV['DB_PASS'] ?? 'NOT SET') . PHP_EOL;