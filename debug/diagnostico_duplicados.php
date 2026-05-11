<?php
require_once "conexion.php";
require_once "auth_check.php";

// Solo administradores
if ($_SESSION['usuario_rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostico de Duplicados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .duplicado {
            background-color: #fff3cd;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mb-4"><i class="bi bi-bug me-2"></i>Diagnostico de Duplicados</h1>
    
    <div class="mb-3">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Menu
        </a>
    </div>
    
    <!-- 1. Buscar expedientes duplicados por numero -->
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0">1. Expedientes con Numero Duplicado</h5>
        </div>
        <div class="card-body">
            <?php
            $sql = "SELECT n_expediente, COUNT(*) as total, GROUP_CONCAT(Id) as ids
                    FROM maestro 
                    GROUP BY n_expediente 
                    HAVING COUNT(*) > 1
                    ORDER BY total DESC";
            
            $stmt = $pdo->query($sql);
            $duplicados = $stmt->fetchAll();
            
            if (count($duplicados) > 0) {
                echo "<div class='alert alert-danger'><strong>ENCONTRADOS " . count($duplicados) . " NUMEROS DE EXPEDIENTE DUPLICADOS!</strong></div>";
                echo "<table class='table table-striped'>";
                echo "<thead><tr><th>Nro Expediente</th><th>Cantidad</th><th>IDs</th><th>Accion</th></tr></thead><tbody>";
                
                foreach ($duplicados as $dup) {
                    echo "<tr class='duplicado'>";
                    echo "<td><strong>{$dup['n_expediente']}</strong></td>";
                    echo "<td><span class='badge bg-danger'>{$dup['total']}</span></td>";
                    echo "<td><code>{$dup['ids']}</code></td>";
                    echo "<td><a href='?ver_detalle={$dup['n_expediente']}' class='btn btn-sm btn-primary'>Ver Detalle</a></td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";
            } else {
                echo "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>No se encontraron numeros de expediente duplicados.</div>";
            }
            ?>
        </div>
    </div>
    
    <!-- 2. Total de registros en maestro -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">2. Estadisticas de la Tabla Maestro</h5>
        </div>
        <div class="card-body">
            <?php
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM maestro");
            $total = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("SELECT COUNT(DISTINCT n_expediente) as unicos FROM maestro");
            $unicos = $stmt->fetch()['unicos'];
            
            echo "<p><strong>Total de registros:</strong> {$total}</p>";
            echo "<p><strong>Expedientes unicos:</strong> {$unicos}</p>";
            
            if ($total > $unicos) {
                $diferencia = $total - $unicos;
                echo "<div class='alert alert-warning'><strong>Diferencia:</strong> {$diferencia} registros duplicados</div>";
            } else {
                echo "<div class='alert alert-success'>No hay duplicados</div>";
            }
            ?>
        </div>
    </div>
    
    <!-- 3. Detalle de un expediente duplicado -->
    <?php if (isset($_GET['ver_detalle'])): ?>
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">3. Detalle del Expediente: <?= htmlspecialchars($_GET['ver_detalle']) ?></h5>
        </div>
        <div class="card-body">
            <?php
            $n_exp = $_GET['ver_detalle'];
            $stmt = $pdo->prepare("SELECT * FROM maestro WHERE n_expediente = :n_exp ORDER BY Id");
            $stmt->execute([':n_exp' => $n_exp]);
            $registros = $stmt->fetchAll();
            
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>ID</th><th>Demandante</th><th>Demandado</th><th>Tribunal</th><th>Fecha Entrada</th></tr></thead><tbody>";
            
            foreach ($registros as $reg) {
                echo "<tr>";
                echo "<td><strong>{$reg['Id']}</strong></td>";
                echo "<td>{$reg['demandante']}</td>";
                echo "<td>{$reg['demandado']}</td>";
                echo "<td>{$reg['id_tribunal']}</td>";
                echo "<td>{$reg['fecha_entrada']}</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
            ?>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





