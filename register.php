<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? $_POST['username'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $rol = isset($_POST['rol']) ? $_POST['rol'] : null;

    if ($username !== null && $password !== null && $rol !== null) {
        // Verificar si el usuario ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
        if ($stmt === false) {
            die('Error en la preparación de la consulta: ' . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Insertar el nuevo usuario
            $stmt = $conn->prepare("INSERT INTO usuarios (username, password, rol) VALUES (?, ?, ?)");
            if ($stmt === false) {
                die('Error en la preparación de la consulta: ' . $conn->error);
            }

            // Hash de la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("sss", $username, $password_hash, $rol);

            if ($stmt->execute()) {
                // Iniciar sesión
                session_start();
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['rol'] = $rol;

                // Redirigir según el rol del usuario
                if ($rol == 'admin') {
                    header("Location: gestionar_productos.php"); // Redirigir al administrador a gestionar productos
                } else {
                    header("Location: index.html"); // Redirigir al cliente a la página principal
                }
                exit();
            } else {
                echo "Error al registrar el usuario: " . $stmt->error;
            }

            // Cerrar la declaración
            $stmt->close();
        } else {
            echo "El usuario ya existe.";
        }
    } else {
        echo "Datos incompletos.";
    }
}

// Cerrar la conexión
$conn->close();
?>
