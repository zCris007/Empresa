<?php 
// Incluir header
require_once '../views/templates/header.php'; 
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center">Sistema de Gestión de Carpintería</h3>
                </div>
                <div class="card-body">
                    <?php 
                    // Mostrar mensajes de error
                    if (isset($_SESSION['errors'])): 
                    ?>
                        <div class="alert alert-danger">
                            <?php 
                            foreach ($_SESSION['errors'] as $error): 
                                echo $error . '<br>';
                            endforeach; 
                            unset($_SESSION['errors']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php 
                    // Mostrar mensajes de la sesión
                    if (isset($_SESSION['message'])): 
                    ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?>">
                            <?php 
                            echo $_SESSION['message']; 
                            unset($_SESSION['message'], $_SESSION['message_type']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo BASE_URL; ?>/auth/authenticate" method="POST">
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                placeholder="Ingrese su correo"
                                value="<?php echo $_SESSION['old_input']['email'] ?? ''; ?>"
                                required
                            >
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Ingrese su contraseña"
                                required
                            >
                        </div>
                        
                        <div class="form-group mb-3 text-end">
                            <a href="<?php echo BASE_URL; ?>/auth/forgot_password" class="text-muted">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Iniciar Sesión
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">
                        © <?php echo date('Y'); ?> Sistema de Gestión de Carpintería
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Incluir footer
require_once '../views/templates/footer.php'; 
?>