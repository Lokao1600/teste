<?php
define('DB_HOST', 'localhost:3306');
define('DB_USER', 'letgru07_Login');
define('DB_PASS', 'Leandro@96200690');
define('DB_NAME', 'letgru07_Login2');

try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    die();
}
?>