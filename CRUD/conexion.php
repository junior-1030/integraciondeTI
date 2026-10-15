<?php
// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'crud_db';
$user = 'root';
$pass = ''; // Por defecto en XAMPP la contraseña está vacía
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$conexion_exitosa = false;
$error_conexion = null;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $conexion_exitosa = true;
} catch (\PDOException $e) {
    $error_conexion = $e->getMessage();
}
?>
