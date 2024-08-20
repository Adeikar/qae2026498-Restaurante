<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar entrada de usuario
    $username = isset($_POST['username']) ? trim($_POST['username']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;

    // Validar que ambos campos estén completos
    if ($username !== null && $password !== null && $username !== '' && $password !== '') {
        // Preparar la consulta para verificar las credenciales del usuario
        $stmt = $conn->prepare("SELECT id, password, rol FROM usuarios WHERE username = ?");
        if ($stmt === false) {
            die('Error en la preparación de la consulta: ' . htmlspecialchars($conn->error));
        }

        // Asociar los parámetros y ejecutar la consulta
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Validar las credenciales
        if ($user && password_verify($password, $user['password'])) {
            // Iniciar sesión y almacenar la información en variables de sesión
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['rol'] = $user['rol'];

            // Redirigir según el rol del usuario
            if ($user['rol'] == 'admin') {
                header("Location: add_producto.php"); // Redirigir al administrador a gestionar productos
            } else {
                header("Location: ordenar.php"); // Redirigir al cliente a la página de ordenar productos
            }
            exit();
        } else {
            // Mostrar mensaje de error si las credenciales son incorrectas
            echo "<p style='color:red;'>Credenciales incorrectas. Por favor, intenta de nuevo.</p>";
        }

        // Cerrar la declaración
        $stmt->close();
    } else {
        // Mostrar mensaje de error si los campos no están completos
        echo "<p style='color:red;'>Por favor, completa todos los campos.</p>";
    }
}

// Cerrar la conexión
$conn->close();
?>
