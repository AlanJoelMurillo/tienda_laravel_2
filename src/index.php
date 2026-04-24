<?php
require 'config.php';

// Solo traemos productos activos y con stock
$stmt = $pdo->query("SELECT p.*, c.nombre as categoria 
                     FROM productos p 
                     LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                     WHERE p.activo = 1 AND p.stock_actual > 0");
$productos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Tienda PHP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Catálogo de Productos</h1>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
        <?php foreach ($productos as $p): ?>
            <div style="border: 1px solid #ccc; padding: 15px; border-radius: 8px;">
                <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                <p><?= htmlspecialchars($p['descripcion']) ?></p>
                <p><strong>Precio: $<?= number_format($p['precio_venta'], 2) ?></strong></p>
                <p><small>Categoría: <?= $p['categoria'] ?></small></p>
                <form action="carrito.php" method="POST">
                    <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                    <button type="submit">Agregar al Carrito</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>