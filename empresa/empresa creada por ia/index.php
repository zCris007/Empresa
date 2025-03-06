<?php
// Iniciar sesión
session_start();

// Cargar configuración
require_once 'config/config.php';
require_once 'config/database.php';

// Cargar helpers
require_once 'helpers/auth_helper.php';
require_once 'helpers/validation_helper.php';

// Conexión a la base de datos
$database = new Database();

// Obtener la URL
$url = isset($_GET['url']) ? $_GET['url'] : 'home';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Controlador
$controller = isset($url[0]) && $url[0] != '' ? $url[0] : 'home';
// Método
$method = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';
// Parámetros
$params = array_slice($url, 2);

// Si el usuario no está autenticado y no está intentando acceder a auth, redirigir al login
if (!isLoggedIn() && $controller != 'auth') {
    redirect('auth/login');
}

// Controlador Auth
if ($controller == 'auth') {
    require_once 'controllers/AuthController.php';
    $controller = new AuthController();
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        require_once 'views/404.php';
    }
}
// Controlador Home/Dashboard
elseif ($controller == 'home') {
    require_once 'controllers/HomeController.php';
    $controller = new HomeController();
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        require_once 'views/404.php';
    }
}
// Controlador Usuarios
elseif ($controller == 'usuarios') {
    require_once 'controllers/UsuarioController.php';
    $controller = new UsuarioController();
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        require_once 'views/404.php';
    }
}
// Controlador Productos
elseif ($controller == 'productos') {
    require_once 'controllers/ProductoController.php';
    $controller = new ProductoController();
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        require_once 'views/404.php';
    }
}
// Controlador Inventario
elseif ($controller == 'inventario') {
    require_once 'controllers/InventarioController.php';
    $controller = new InventarioController();
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        require_once 'views/404.php';
    }
}
// Controlador Facturas
elseif ($controller == 'facturas') {
    require_once 'controllers/FacturaController.php';
    $controller = new FacturaController();
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        require_once 'views/404.php';
    }
}
// Controlador Nómina
elseif ($controller == 'nomina') {
    require_once 'controllers/NominaController.php';
    $controller = new NominaController();
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        require_once 'views/404.php';
    }
}
// Página no encontrada
else {
    require_once 'views/404.php';
}
?>