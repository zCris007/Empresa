<?php
class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Mostrar formulario de login
     */
    public function login() {
        // Si ya está logueado, redirigir al dashboard
        if (isLoggedIn()) {
            redirect('home');
        }
        
        // Cargar vista
        require_once 'views/auth/login.php';
    }
    
    /**
     * Procesar formulario de login
     */
    public function authenticate() {
        // Si ya está logueado, redirigir al dashboard
        if (isLoggedIn()) {
            redirect('home');
        }
        
        // Verificar si es POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/login');
        }
        
        // Obtener y sanitizar datos
        $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Validar datos
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = 'El email es obligatorio';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email no es válido';
        }
        
        if (empty($password)) {
            $errors['password'] = 'La contraseña es obligatoria';
        }
        
        // Si hay errores, volver al formulario
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = ['email' => $email];
            redirect('auth/login');
            return;
        }
        
        // Buscar usuario por email
        $this->db->query('SELECT * FROM usuarios WHERE email = :email AND estado = "activo"');
        $this->db->bind(':email', $email);
        $user = $this->db->single();

        // Si no existe el usuario
        if (!$user) {
            $_SESSION['errors'] = ['login' => 'Credenciales incorrectas'];
            redirect('auth/login');
            return;
        }
        
        // Verificar contraseña
        if (!verifyPassword($password, $user->password)) {
            $_SESSION['errors'] = ['login' => 'Credenciales incorrectas'];
            redirect('auth/login');
            return;
        }
        
        // Iniciar sesión
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_type'] = $user->tipo_usuario;
        $_SESSION['user_name'] = $user->nombre . ' ' . $user->apellido;
        $_SESSION['csrf_token'] = generateToken();
        
        // Crear registro de inicio de sesión
        $this->db->query('INSERT INTO logs_sesiones (usuario_id, ip_address, navegador) VALUES (:usuario_id, :ip, :navegador)');
        $this->db->bind(':usuario_id', $user->id);
        $this->db->bind(':ip', $_SERVER['REMOTE_ADDR']);
        $this->db->bind(':navegador', $_SERVER['HTTP_USER_AGENT']);
        $this->db->execute();
        
        // Redirigir según el tipo de usuario
        switch ($user->tipo_usuario) {
            case 'propietario':
                redirect('home/dashboard');
                break;
            case 'administrador':
                redirect('home/admin');
                break;
            case 'usuario':
                redirect('home/panel');
                break;
            default:
                redirect('home');
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        // Registrar cierre de sesión
        if (isLoggedIn()) {
            $this->db->query('INSERT INTO logs_sesiones (usuario_id, ip_address, navegador, tipo_log) VALUES (:usuario_id, :ip, :navegador, "logout")');
            $this->db->bind(':usuario_id', getUserId());
            $this->db->bind(':ip', $_SERVER['REMOTE_ADDR']);
            $this->db->bind(':navegador', $_SERVER['HTTP_USER_AGENT']);
            $this->db->execute();
        }
        
        // Destruir sesión
        session_unset();
        session_destroy();
        
        // Limpiar cookies de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Redirigir al login
        redirect('auth/login');
    }
    
    /**
     * Mostrar formulario de registro
     */
    public function register() {
        // Solo propietario y administradores pueden registrar usuarios
        requirePermission('administrador');
        
        // Cargar vista de registro
        require_once 'views/auth/register.php';
    }
    
    /**
     * Procesar registro de usuario
     */
    public function store() {
        // Solo propietario y administradores pueden registrar usuarios
        requirePermission('administrador');
        
        // Verificar si es POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/register');
        }
        
        // Validar datos
        $rules = [
            'nombre' => [
                ['validateRequired', 'Nombre'],
                ['validateMinLength', 'Nombre', 2],
                ['validateMaxLength', 'Nombre', 50]
            ],
            'apellido' => [
                ['validateRequired', 'Apellido'],
                ['validateMinLength', 'Apellido', 2],
                ['validateMaxLength', 'Apellido', 50]
            ],
            'email' => [
                ['validateRequired', 'Email'],
                ['validateEmail'],
                ['validateMaxLength', 'Email', 100]
            ],
            'password' => [
                ['validateRequired', 'Contraseña'],
                ['validateMinLength', 'Contraseña', 8],
                ['validateMaxLength', 'Contraseña', 50]
            ],
            'password_confirm' => [
                ['validateRequired', 'Confirmación de Contraseña'],
                ['validateMatch', 'password', 'Contraseñas']
            ],
            'tipo_usuario' => [
                ['validateRequired', 'Tipo de Usuario'],
                ['validateInArray', 'Tipo de Usuario', ['propietario', 'administrador', 'usuario']]
            ]
        ];
        
        // Obtener y sanitizar datos
        $data = [
            'nombre' => sanitize($_POST['nombre']),
            'apellido' => sanitize($_POST['apellido']),
            'email' => sanitize($_POST['email']),
            'password' => $_POST['password'],
            'password_confirm' => $_POST['password_confirm'],
            'tipo_usuario' => sanitize($_POST['tipo_usuario']),
            'telefono' => isset($_POST['telefono']) ? sanitize($_POST['telefono']) : '',
            'direccion' => isset($_POST['direccion']) ? sanitize($_POST['direccion']) : ''
        ];
        
        // Validar datos
        $errors = validate($data, $rules);
        
        // Verificar si el email ya existe
        $this->db->query('SELECT id FROM usuarios WHERE email = :email');
        $this->db->bind(':email', $data['email']);
        if ($this->db->single()) {
            $errors['email'] = 'El email ya está registrado';
        }
        
        // Si hay errores, volver al formulario
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            redirect('auth/register');
            return;
        }
        
        // Hash de contraseña
        $passwordHash = hashPassword($data['password']);
        
        // Preparar consulta de inserción
        $this->db->query('INSERT INTO usuarios (nombre, apellido, email, password, tipo_usuario, telefono, direccion) 
                          VALUES (:nombre, :apellido, :email, :password, :tipo_usuario, :telefono, :direccion)');
        
        // Bindear parámetros
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $passwordHash);
        $this->db->bind(':tipo_usuario', $data['tipo_usuario']);
        $this->db->bind(':telefono', $data['telefono']);
        $this->db->bind(':direccion', $data['direccion']);
        
        // Ejecutar consulta
        try {
            $this->db->execute();
            
            // Registrar log de creación de usuario
            $this->db->query('INSERT INTO logs_sistema (usuario_id, accion, descripcion) 
                              VALUES (:usuario_id, "registro_usuario", :descripcion)');
            $this->db->bind(':usuario_id', getUserId());
            $this->db->bind(':descripcion', 'Registro de nuevo usuario: ' . $data['email']);
            $this->db->execute();
            
            // Mensaje de éxito
            $_SESSION['message'] = 'Usuario registrado exitosamente';
            $_SESSION['message_type'] = 'success';
            
            // Redirigir al listado de usuarios
            redirect('usuarios');
        } catch (Exception $e) {
            // Mensaje de error
            $_SESSION['message'] = 'Error al registrar usuario: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            
            redirect('auth/register');
        }
    }
    
    /**
     * Recuperación de contraseña
     */
    public function forgotPassword() {
        // Mostrar formulario de recuperación
        require_once 'views/auth/forgot_password.php';
    }
    
    /**
     * Procesar solicitud de recuperación de contraseña
     */
    public function resetPassword() {
        // Lógica para generar y enviar token de recuperación
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/forgot_password');
        }
        
        $email = sanitize($_POST['email']);
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors'] = ['email' => 'Email no válido'];
            redirect('auth/forgot_password');
            return;
        }
        
        // Buscar usuario
        $this->db->query('SELECT id, nombre, email FROM usuarios WHERE email = :email AND estado = "activo"');
        $this->db->bind(':email', $email);
        $user = $this->db->single();
        
        if (!$user) {
            // No mostrar si el email no existe por seguridad
            $_SESSION['message'] = 'Si el email existe, recibirá instrucciones de recuperación';
            $_SESSION['message_type'] = 'info';
            redirect('auth/login');
            return;
        }
        
        // Generar token de recuperación
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Guardar token en base de datos
        $this->db->query('INSERT INTO recuperacion_password (usuario_id, token, expira) 
                          VALUES (:usuario_id, :token, :expira)');
        $this->db->bind(':usuario_id', $user->id);
        $this->db->bind(':token', $token);
        $this->db->bind(':expira', $expira);
        
        try {
            $this->db->execute();
            
            // Enviar email de recuperación (implementar función de envío de email)
            $resetLink = BASE_URL . '/auth/reset_password?token=' . $token;
            
            // TODO: Implementar envío de email con librería PHPMailer
            
            $_SESSION['message'] = 'Se ha enviado un enlace de recuperación a su correo';
            $_SESSION['message_type'] = 'success';
            
            redirect('auth/login');
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error al procesar la solicitud';
            $_SESSION['message_type'] = 'danger';
            
            redirect('auth/forgot_password');
        }
    }
}
?>