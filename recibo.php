<?php
include 'db_connection.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    die("No estás logueado.");
}

$usuario_id = $_SESSION['user_id']; // Usa el ID del usuario

$total = 0;
$orden_id = null;
$detalles = []; // Array para almacenar los detalles

if (isset($_GET['orden_id'])) {
    $orden_id = intval($_GET['orden_id']); // Obtener el ID de la orden de la URL

    // Consultar la orden
    $sql = "SELECT * FROM Ordenes WHERE id = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $orden_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $orden = $result->fetch_assoc();
        $total = $orden['total'];
        
        // Consultar los detalles de la orden
        $sql = "SELECT * FROM DetallesOrden WHERE id_orden = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orden_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($detalle = $result->fetch_assoc()) {
            $detalles[] = $detalle;
        }
    } else {
        echo "No se encontró ninguna orden.";
        $conn->close();
        exit();
    }

    $stmt->close();
} else {
    echo "ID de orden no especificado.";
    $conn->close();
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo - Sistema de Gestión de Restaurante</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }

        .invoice {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }

        .invoice-header {
            border-bottom: 2px solid #343a40;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .invoice-header h1 {
            font-size: 24px;
            margin: 0;
            color: #343a40;
        }

        .invoice-header p {
            margin: 5px 0;
            color: #495057;
        }

        .invoice-details {
            margin-bottom: 20px;
        }

        .invoice-details h2 {
            font-size: 20px;
            color: #343a40;
            margin-bottom: 10px;
        }

        .invoice-details p {
            font-size: 18px;
            color: #495057;
            margin: 5px 0;
        }

        .invoice-items {
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }

        .invoice-items ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .invoice-items li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .invoice-items li:last-child {
            border-bottom: none;
        }

        .invoice-items span {
            color: #495057;
        }

        .invoice-footer {
            border-top: 2px solid #343a40;
            padding-top: 10px;
            margin-top: 20px;
            text-align: center;
        }

        .invoice-footer button {
            background-color: #dc3545;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .invoice-footer button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="invoice-header">
            <h1>Recibo de Orden</h1>
            <p>ID de Orden: #<?php echo htmlspecialchars($orden_id); ?></p>
        </div>
        
        <div class="invoice-details">
            <p>Total: $<?php echo number_format($total, 2); ?></p>
        </div>

        <div class="invoice-items">
            <h2>Detalles:</h2>
            <ul>
                <?php foreach ($detalles as $detalle): ?>
                    <li>
                        <span>Producto ID: <?php echo htmlspecialchars($detalle['id_producto']); ?></span>
                        <span>Cantidad: <?php echo htmlspecialchars($detalle['cantidad']); ?></span>
                        <span>Precio Unitario: $<?php echo number_format($detalle['precio_unitario'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="invoice-footer">
            <form action="logout.php" method="POST">
                <button type="submit">Cerrar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>

