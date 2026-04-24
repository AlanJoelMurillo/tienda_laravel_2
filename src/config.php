<?php
// Datos del servidor remoto
$host = '192.168.1.100'; // CAMBIA POR LA IP DE TU WINDOWS SERVER
$db   = 'db_almacen';
$user = 'tu_usuario_remoto';
$pass = 'tu_password_remoto';
$port = '3306'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>