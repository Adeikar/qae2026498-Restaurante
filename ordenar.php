<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos de la orden
    $productos = isset($_POST['producto']) ? $_POST['producto'] : [];
    $cantidades = isset($_POST['cantidad']) ? $_POST['cantidad'] : [];

    // Validar que las cantidades y productos no estén vacíos
    if (empty($productos) || empty($cantidades)) {
        die("Los productos o cantidades están vacíos.");
    }

    // Inicializar variables
    $total = 0;
    $productos_precios = []; // Para almacenar precios de productos

    // Validar y calcular el total de la orden
    for ($i = 0; $i < count($productos); $i++) {
        $producto_id = filter_var($productos[$i], FILTER_VALIDATE_INT);
        $cantidad = filter_var($cantidades[$i], FILTER_VALIDATE_INT);

        // Verificar que el ID del producto y la cantidad sean válidos
        if ($producto_id === false || $cantidad === false || $cantidad <= 0) {
            die("ID del producto o cantidad no válida.");
        }

        // Obtener el precio del producto
        $sql = "SELECT precio FROM Productos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $producto = $result->fetch_assoc();

        if (!$producto) {
            die("Producto no encontrado.");
        }

        $precio = $producto['precio'];
        if (!is_numeric($precio) || $precio < 0) {
            die("Precio del producto no válido.");
        }

        $total += $precio * $cantidad;
        $productos_precios[$producto_id] = $precio;
    }

    // Insertar orden en la tabla Ordenes
    session_start();
    $usuario_id = $_SESSION['user_id']; // Obtener el ID del usuario logueado

    if (!$usuario_id) {
        die("No se ha iniciado sesión.");
    }

    $sql = "INSERT INTO Ordenes (id_usuario, total) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $usuario_id, $total);

    if ($stmt->execute()) {
        $orden_id = $stmt->insert_id;

        // Insertar detalles de la orden en la tabla DetallesOrden
        foreach ($productos as $index => $producto_id) {
            $cantidad = $cantidades[$index];
            $precio_unitario = $productos_precios[$producto_id];

            $sql = "INSERT INTO DetallesOrden (id_orden, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $orden_id, $producto_id, $cantidad, $precio_unitario);
            $stmt->execute();
        }

        // Redirigir al usuario a la página de recibo con el ID de la orden
        header("Location: recibo.php?orden_id=" . $orden_id);
        exit();
    } else {
        echo "Error al insertar la orden: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
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
            max-width: 500px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: bold;
        }

        select,
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
        }

        select {
            height: 100px;
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
    </style>
</head>
<body>
    <h1>Sistema de Gestión de Restaurante</h1>

    <!-- Ordenar Productos -->
    <h2>Ordenar Productos</h2>
    <form action="ordenar.php" method="POST">
        <label for="producto">Seleccionar Producto:</label>
        <select id="producto" name="producto[]" multiple required>
            <?php
            // Obtener productos de la base de datos
            $sql = "SELECT id, nombre FROM Productos";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Mostrar productos
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nombre']) . '</option>';
                }
            } else {
                echo '<option value="">No hay productos disponibles</option>';
            }
            ?>
        </select>
        
        <label for="cantidad">Cantidad:</label>
        <input type="number" id="cantidad" name="cantidad[]" min="1" required>
        
        <button type="submit">Ordenar</button>
    </form>
    
    <?php
    $conn->close();
    ?>
</body>
</html>
