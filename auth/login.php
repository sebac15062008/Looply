<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Looply</title>
    <?php renderBootstrapHead('..'); ?>
</head>
<body>
    <div class="container login-container">
        <div class="card p-4">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">Looply</h2>
                <p class="text-muted">Conecta con tus compañeros de curso</p>
            </div>
            <form>
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" placeholder="nombre@ejemplo.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">Entrar</button>
            </form>
            <hr>
            <div class="text-center">
                <a href="register.php" class="text-decoration-none">¿No tienes cuenta? Regístrate</a>
            </div>
        </div>
    </div>
</body>
</html>
