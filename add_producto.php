<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar entrada de usuario
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
    $precio = isset($_POST['precio']) ? trim($_POST['precio']) : null;
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

    // Validar que los campos necesarios no estén vacíos
    // y que nombre y descripcion solo contengan letras
    if ($nombre !== null && $nombre !== '' && $precio !== null && $precio !== '' && is_numeric($precio)) {
        if (preg_match("/^[a-zA-Z\s]+$/", $nombre) && preg_match("/^[a-zA-Z\s]*$/", $descripcion)) {
            // Preparar la consulta para insertar el producto
            $sql = "INSERT INTO Productos (nombre, precio, descripcion) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                die('Error en la preparación de la consulta: ' . htmlspecialchars($conn->error));
            }

            // Asociar los parámetros y ejecutar la consulta
            $stmt->bind_param("sds", $nombre, $precio, $descripcion);

            if ($stmt->execute()) {
                echo "<p style='color:green;'>Producto agregado exitosamente.</p>";
            } else {
                echo "<p style='color:red;'>Error: " . htmlspecialchars($stmt->error) . "</p>";
            }

            // Cerrar la declaración
            $stmt->close();
        } else {
            echo "<p style='color:red;'>El nombre y la descripción solo deben contener letras y espacios.</p>";
        }
    } else {
        echo "<p style='color:red;'>Datos incompletos o incorrectos. Asegúrate de que todos los campos estén llenos correctamente.</p>";
    }

    // Cerrar la conexión
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Restaurante</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 50px;
        }

        h1 {
            color: #343a40;
            margin-bottom: 30px;
        }

        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            width: 100%;
            max-width: 400px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #28a745;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        h2 {
            color: #495057;
            margin-bottom: 20px;
            font-size: 24px;
        }

        form.logout-form {
            background-color: transparent;
            box-shadow: none;
            width: auto;
            margin-bottom: 30px;
        }

        form.logout-form button {
            background-color: #dc3545;
            margin-top: 0;
            width: auto;
            padding: 8px 16px;
            font-size: 14px;
        }

        form.logout-form button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <h1>Sistema de Gestión de Restaurante</h1>


    <!-- Registro de Usuario -->
    <h2>Registro</h2>
    <form action="register.php" method="POST">
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        <label for="rol">Rol:</label>
        <select id="rol" name="rol" required>
            <option value="admin">Admin</option>
            <option value="cliente">Cliente</option>
        </select>
        <button type="submit">Registrarse</button>
    </form>

    <!-- Gestión de Productos -->
    <h2>Gestión de Productos</h2>
    <form action="add_producto.php" method="POST">
        <label for="nombre">Nombre del Producto:</label>
        <input type="text" id="nombre" name="nombre" required>
        <label for="precio">Precio:</label>
        <input type="number" step="0.01" id="precio" name="precio" required>
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion"></textarea>
        <button type="submit">Agregar Producto</button>
    </form>


        <!-- Botón de Cerrar Sesión -->
    <form action="logout.php" method="POST" class="logout-form">
        <button type="submit">Cerrar Sesión</button>
    </form>
    
</body>
</html>
