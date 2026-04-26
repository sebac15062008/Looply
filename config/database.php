<?php

$ERROR_CONNECTION = false;
$ERROR_CONNECTION_MESSAGES = "";

class connectionDatabase
{
  private string $DB_HOST     = "localhost";
  private string $DB_PORT     = "3306";
  private string $DB_DATABASE = "Looply";
  private string $DB_USERNAME = "root";
  private string $DB_PASSWORD = "";
  public  $con;

  public function __construct()
  {
    $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
      $dsn        = "mysql:host={$this->DB_HOST};port={$this->DB_PORT};dbname={$this->DB_DATABASE};charset=utf8mb4";
      $this->con  = new PDO($dsn, $this->DB_USERNAME, $this->DB_PASSWORD, $options);
    } catch (PDOException $e) {
      global $ERROR_CONNECTION, $ERROR_CONNECTION_MESSAGES;
      $ERROR_CONNECTION          = true;
      $ERROR_CONNECTION_MESSAGES = $e->getMessage();
    }
  }
}

$PRUEBA_DE_CONEXION = new connectionDatabase();

if ($ERROR_CONNECTION) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>503 – Sin conexión</title>
  <style>
    body{margin:0;background:#0f172a;display:flex;flex-direction:column;align-items:center;
         justify-content:center;min-height:100vh;font-family:sans-serif;color:#f1f5f9;text-align:center}
    h1{font-size:8rem;margin:0;font-weight:900;color:#ef4444}
    p{font-size:1.1rem;margin-top:1rem;opacity:.8;max-width:400px}
    pre{margin-top:2rem;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);
        border-radius:10px;padding:1.2rem 1.8rem;font-family:monospace;font-size:.85rem;
        max-width:520px;width:90%;white-space:pre-wrap;word-break:break-all}
  </style>
</head>
<body>
  <h1>503</h1>
  <p>El servidor MySQL no está disponible. Revisa tu conexión e intenta de nuevo.</p>
  <pre><?php echo htmlspecialchars($ERROR_CONNECTION_MESSAGES); ?></pre>
</body>
</html>
<?php
  die();
}