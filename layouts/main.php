<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Looply - Red Social'; ?></title>
    <?php renderBootstrapHead('..'); ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="../index.php">Looply</a>
            <div class="d-flex">
                <a href="../auth/login.php" class="btn btn-outline-primary me-2">Login</a>
                <a href="../auth/register.php" class="btn btn-primary">Registrarse</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar / Menú -->
            <div class="col-md-3 d-none d-md-block">
                <div class="sidebar-sticky">
                    <?php include(__DIR__ . "/../includes/components/sidebar.php"); ?>
                </div>
            </div>

            <!-- Contenido Principal -->
            <main class="col-md-9 col-lg-7">
                <?php echo $content ?? ''; ?>
            </main>

            <!-- Sugerencias / Derecha (Opcional) -->
            <div class="col-lg-2 d-none d-lg-block">
                <div class="card p-3">
                    <h6 class="fw-bold">Sugerencias</h6>
                    <small class="text-muted">Próximamente...</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
