<?php
// Helper para gestión de autenticación

/**
 * Verifica si el usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Obtiene el ID del usuario logueado
 */
function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : false;
}

/**
 * Obtiene el tipo de usuario logueado
 */
function getUserType() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : false;
}

/**
 * Verifica si el usuario es propietario
 */
function isPropietario() {
    return getUserType() === 'propietario';
}

/**
 * Verifica si el usuario es administrador
 */
function isAdmin() {
    return getUserType() === 'administrador' || getUserType() === 'propietario';
}

/**
 * Verifica si el usuario tiene permisos para acceder a cierta área
 */
function checkPermission($requiredType = 'usuario') {
    // Si no está logueado, no tiene permiso
    if (!isLoggedIn()) {
        return false;
    }
    
    $userType = getUserType();
    
    // Propietario tiene acceso a todo
    if ($userType === 'propietario') {
        return true;
    }
    
    // Administrador tiene acceso a todo excepto áreas exclusivas de propietario
    if ($userType === 'administrador' && $requiredType !== 'propietario') {
        return true;
    }
    
    // Usuario normal solo tiene acceso a áreas de usuario
    if ($userType === 'usuario' && $requiredType === 'usuario') {
        return true;
    }
    
    return false;
}

/**
 * Verifica permisos y redirige si no tiene acceso
 */
function requirePermission($requiredType = 'usuario') {
    if (!checkPermission($requiredType)) {
        // Guardar mensaje de error
        $_SESSION['message'] = 'No tienes permisos para acceder a esta área';
        $_SESSION['message_type'] = 'danger';
        
        // Redirigir según el tipo de usuario actual
        if (isLoggedIn()) {
            redirect('home');
        } else {
            redirect('auth/login');
        }
    }
}

/**
 * Crear hash de contraseña
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verificar contraseña
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>