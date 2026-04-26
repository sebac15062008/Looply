<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Looply</title>
    <?php renderBootstrapHead('..'); ?>
</head>
<body>
    <div class="container login-container">
        <div class="card p-4">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">Únete a Looply</h2>
                <p class="text-muted">Es rápido y fácil.</p>
            </div>
            <form>
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" class="form-control" placeholder="Sebastian Cuevas">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre de usuario</label>
                    <input type="text" class="form-control" placeholder="JuanPerez22">
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" placeholder="nombre@ejemplo.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" placeholder="Mínimo 8 caracteres">
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold">Registrarte</button>
            </form>
            <hr>
            <div class="text-center">
                <a href="login.php" class="text-decoration-none">¿Ya tienes una cuenta? Inicia sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
