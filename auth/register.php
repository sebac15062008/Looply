<?php
require_once(__DIR__ . "/../includes/bootstrap.php");
require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../includes/queries.php");

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = trim($_POST['password']  ?? '');

    if (!empty($full_name) && !empty($username) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $db = (new connectionDatabase())->con;

        try {
            q_insert_user($db, $full_name, $username, $email, $hashed_password);
            header("Location: login.php?registered=1");
            exit();
        } catch (PDOException $e) {
            $error = ($e->getCode() == 23000)
                ? "El usuario o correo ya existe."
                : "Error al registrar: " . $e->getMessage();
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Looply</title>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/register.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body style="background: var(--chat-bg); margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;">

<form class="form" method="POST" style="margin: 20px 0;">
    <div class="text-center mb-3">
        <h2 style="color: var(--text-bright); font-weight: bold;">Looply</h2>
    </div>

  <?php if ($error): ?>
      <div class="alert alert-danger" style="font-size: 0.8rem; padding: 5px 10px;"><?php echo $error; ?></div>
  <?php endif; ?>

  <div class="flex-column">
    <label>Name </label>
  </div>
  <div class="inputForm">
    <svg
      height="60"
      viewBox="0 -9 32 32"
      width="40"
      xmlns="http://www.w3.org/2000/svg"
    >
      <g id="Layer_3" data-name="Layer 3">
        <path
          d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"
        ></path>
      </g>
    </svg>
    <input type="text" name="full_name" class="input" placeholder="Enter your Name" required />
  </div>

  <div class="flex-column">
    <label>Username </label>
  </div>
  <div class="inputForm">
    <svg height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #8b8e98;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
    <input type="text" name="username" class="input" placeholder="Enter your Username" required />
  </div>

  <div class="input-spacer" style="margin-top: 10px;"></div>

  <div class="flex-column">
    <label>Email </label>
  </div>
  <div class="inputForm">
    <svg
      height="20"
      viewBox="0 0 32 32"
      width="20"
      xmlns="http://www.w3.org/2000/svg"
    >
      <g id="Layer_3" data-name="Layer 3">
        <path
          d="m30.853 13.87a15 15 0 0 0 -29.729 4.082 15.1 15.1 0 0 0 12.876 12.918 15.6 15.6 0 0 0 2.016.13 14.85 14.85 0 0 0 7.715-2.145 1 1 0 1 0 -1.031-1.711 13.007 13.007 0 1 1 5.458-6.529 2.149 2.149 0 0 1 -4.158-.759v-10.856a1 1 0 0 0 -2 0v1.726a8 8 0 1 0 .2 10.325 4.135 4.135 0 0 0 7.83.274 15.2 15.2 0 0 0 .823-7.455zm-14.853 8.13a6 6 0 1 1 6-6 6.006 6.006 0 0 1 -6 6z"
        ></path>
      </g>
    </svg>
    <input type="email" name="email" class="input" placeholder="Enter your Email" required />
  </div>

  <div class="flex-column">
    <label>Password </label>
  </div>
  <div class="inputForm">
    <svg
      height="20"
      viewBox="-64 0 512 512"
      width="20"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0"
      ></path>
      <path
        d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.167969 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.167969 16-16 16zm0 0"
      ></path>
    </svg>
    <input type="password" name="password" class="input" placeholder="Enter your Password" required />
  </div>

  <button type="submit" class="button-submit">Sign Up</button>
  <p class="p">Already have a account? <a href="login.php" class="span">login</a></p>
</form>

</body>
</html>
