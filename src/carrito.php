<?php
session_start();
require 'config.php';

// Agregar al carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_producto'])) {
    $id = $_POST['id_producto'];
    $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + 1;
    header("Location: carrito.php");
    exit;
}

// Vaciar carrito
if (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
    header("Location: carrito.php");
    exit;
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
                    <td><?= $p['nombre'] ?></td>
                    <td><?= $cantidad ?></td>
                    <td>$<?= number_format($p['precio_venta'], 2) ?></td>
                    <td>$<?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3>Total: $<?= number_format($total, 2) ?></h3>
        <button onclick="alert('¡Compra simulada! Aquí conectarías con una pasarela de pago.')">Finalizar Compra</button>
        <a href="carrito.php?vaciar=1">Vaciar Carrito</a>
    <?php endif; ?>
</body>
</html>