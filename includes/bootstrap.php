<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn(): void
{
    if (!isLoggedIn()) {
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

function renderBootstrapHead(string $basePath = ''): void
{
    $basePath = rtrim($basePath, '/');
    if ($basePath !== '') {
        $basePath .= '/';
    }
?>
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/custom.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php
}
