<?php
// Configuración global de la aplicación
define('BASE_URL', 'http://localhost/carpinteria');
define('APP_NAME', 'Sistema de Gestión de Carpintería');
define('APP_VERSION', '1.0.0');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'carpinteria_db');

// Configuración de sesiones
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true
]);

// Zonas horarias
date_default_timezone_set('America/Mexico_City');

// Mostrar errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Funciones de utilidad
function redirect($url) {
    header('Location: ' . BASE_URL . '/' . $url);
    exit;
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function checkToken($token) {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}
?>