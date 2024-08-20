<?php
session_start();
session_unset(); // Destruye todas las variables de sesión
session_destroy(); // Destruye la sesión

// Redirige al usuario a la página de inicio o login
header("Location: index.html");
exit();
?>
