<?php
session_start();
require 'config.php';

// 1. Agregar al carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_producto'])) {
    $id = $_POST['id_producto'];
    $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + 1;
    header("Location: carrito.php");
    exit;
}

// 2. Vaciar carrito
if (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
    header("Location: carrito.php");
    exit;
}

// 3. Lógica para FINALIZAR COMPRA y bajar stock
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalizar_compra'])) {
    if (!empty($_SESSION['carrito'])) {
        try {
            // Iniciamos transacción para seguridad de datos
            $pdo->beginTransaction();

            foreach ($_SESSION['carrito'] as $id => $cantidad) {
                // Consultamos el stock actual con bloqueo (FOR UPDATE) para evitar condiciones de carrera
                $stmt = $pdo->prepare("SELECT stock_actual, nombre FROM productos WHERE id_producto = ? FOR UPDATE");
                $stmt->execute([$id]);
                $producto = $stmt->fetch();

                if ($producto) {
                    if ($producto['stock_actual'] >= $cantidad) {
                        // Restamos el stock
                        $update = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id_producto = ?");
                        $update->execute([$cantidad, $id]);
                    } else {
                        throw new Exception("Stock insuficiente para: " . $producto['nombre']);
                    }
                }
            }

            // Si todo salió bien, guardamos cambios de forma permanente
            $pdo->commit();
            unset($_SESSION['carrito']);
            $mensaje = "<div style='color: green;'>¡Compra finalizada con éxito! Stock actualizado.</div>";
        } catch (Exception $e) {
            // Si algo falla, revertimos cualquier cambio hecho durante este ciclo
            $pdo->rollBack();
            $mensaje = "<div style='color: red;'>Error en la compra: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <a href="index.php">← Volver al catálogo</a>
    <h1>Tu Carrito</h1>

    <?= $mensaje ?>
    
    <?php if (empty($_SESSION['carrito'])): ?>
        <p>El carrito está vacío.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach ($_SESSION['carrito'] as $id => $cantidad): 
                    $stmt = $pdo->prepare("SELECT nombre, precio_venta FROM productos WHERE id_producto = ?");
                    $stmt->execute([$id]);
                    $p = $stmt->fetch();
                    $subtotal = $p['precio_venta'] * $cantidad;
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= $cantidad ?></td>
                    <td>$<?= number_format($p['precio_venta'], 2) ?></td>
                    <td>$<?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3>Total: $<?= number_format($total, 2) ?></h3>

        <form method="POST">
            <button type="submit" name="finalizar_compra">Finalizar Compra</button>
        </form>

        <a href="carrito.php?vaciar=1">Vaciar Carrito</a>
    <?php endif; ?>
</body>
</html>