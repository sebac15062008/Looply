<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function esta_logueado(): bool
{
    return isset($_SESSION['user_id']);
}

function redirigir_si_no_logueado(): void
{
    if (!esta_logueado()) {
        // Obtenemos la ruta base para redirigir correctamente al login
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        // Buscamos dónde está la raíz del proyecto para evitar errores de ruta
        $currentPath = $_SERVER['PHP_SELF'];
        $pathParts = explode('/', trim($currentPath, '/'));
        
        // Si el proyecto está en una subcarpeta (como /Looply/), mantenemos esa base
        $base = "";
        if (!empty($pathParts) && $pathParts[0] === 'Looply') {
            $base = "/Looply";
        }

        header("Location: $base/auth/login.php");
        exit();
    }
}

function renderizar_cabecera_bootstrap(string $rutaBase = ''): void
{
    $rutaBase = rtrim($rutaBase, '/');
    if ($rutaBase !== '') {
        $rutaBase .= '/';
    }
?>
  <link rel="stylesheet" href="<?php echo $rutaBase; ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?php echo $rutaBase; ?>assets/css/custom.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php
}

function renderizar_script_bootstrap(string $rutaBase = ''): void
{
    $rutaBase = rtrim($rutaBase, '/');
    if ($rutaBase !== '') {
        $rutaBase .= '/';
    }
?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const esEnSubcarpeta = window.location.pathname.includes('/auth/') || window.location.pathname.includes('/views/');
            const base = esEnSubcarpeta ? '../' : '';
            
            if (window.location.search.includes('user=')) {
                window.location.href = 'index.php';
            } else {
                window.location.href = base + 'index.php';
            }
        }
    });
  </script>
<?php
}
