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
    <title>Diagnostico de Tribunales Duplicados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .duplicado {
            background-color: #fff3cd;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mb-4"><i class="bi bi-building me-2"></i>Diagnostico de Tribunales Duplicados</h1>
    
    <div class="mb-3">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Menu
        </a>
    </div>
    
    <div class="alert alert-warning">
        <h5><i class="bi bi-exclamation-triangle me-2"></i>Problema Detectado</h5>
        <p>Tienes tribunales con el mismo <code>id_tribunal</code> pero diferentes nombres. Esto causa que el sistema no pueda distinguir entre ellos al editar.</p>
    </div>
    
    <!-- 1. Buscar tribunales con ID duplicado -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Tribunales con ID Duplicado</h5>
        </div>
        <div class="card-body">
            <?php
            $sql = "SELECT id_tribunal, COUNT(*) as total, GROUP_CONCAT(tribunal SEPARATOR ' | ') as nombres
                    FROM tribunales 
                    GROUP BY id_tribunal 
                    HAVING COUNT(*) > 1
                    ORDER BY id_tribunal";
            
            $stmt = $pdo->query($sql);
            $duplicados = $stmt->fetchAll();
            
            if (count($duplicados) > 0) {
                echo "<div class='alert alert-danger'><strong>ENCONTRADOS " . count($duplicados) . " IDs DE TRIBUNAL DUPLICADOS!</strong></div>";
                echo "<table class='table table-striped'>";
                echo "<thead><tr><th>ID Tribunal</th><th>Cantidad</th><th>Nombres</th></tr></thead><tbody>";
                
                foreach ($duplicados as $dup) {
                    echo "<tr class='duplicado'>";
                    echo "<td><strong>{$dup['id_tribunal']}</strong></td>";
                    echo "<td><span class='badge bg-danger'>{$dup['total']}</span></td>";
                    echo "<td>{$dup['nombres']}</td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";
            } else {
                echo "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>No se encontraron IDs de tribunal duplicados.</div>";
            }
            ?>
        </div>
    </div>
    
    <!-- 2. Detalle completo de tribunales -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Todos los Tribunales (Ordenados por ID)</h5>
        </div>
        <div class="card-body">
            <?php
            $stmt = $pdo->query("SELECT * FROM tribunales ORDER BY id_tribunal, tribunal");
            $todos = $stmt->fetchAll();
            
            echo "<table class='table table-bordered table-sm'>";
            echo "<thead><tr><th>ID Tribunal</th><th>Nombre del Tribunal</th><th>Registros en Maestro</th></tr></thead><tbody>";
            
            $id_anterior = null;
            foreach ($todos as $trib) {
                // Contar cuantos expedientes usan este tribunal
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM maestro WHERE id_tribunal = :id");
                $stmtCount->execute([':id' => $trib['id_tribunal']]);
                $uso = $stmtCount->fetchColumn();
                
                // Resaltar si es duplicado
                $clase = ($id_anterior === $trib['id_tribunal']) ? 'duplicado' : '';
                
                echo "<tr class='{$clase}'>";
                echo "<td><strong>{$trib['id_tribunal']}</strong></td>";
                echo "<td>{$trib['tribunal']}</td>";
                echo "<td><span class='badge bg-secondary'>{$uso} expedientes</span></td>";
                echo "</tr>";
                
                $id_anterior = $trib['id_tribunal'];
            }
            
            echo "</tbody></table>";
            ?>
        </div>
    </div>
    
    <!-- 3. Solucion recomendada -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Solucion Recomendada</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-danger">
                <h6><i class="bi bi-exclamation-octagon me-2"></i>PROBLEMA CRITICO IDENTIFICADO</h6>
                <p>La tabla <code>maestro</code> solo guarda el <strong>id_tribunal</strong> (numero), no el nombre del tribunal. 
                Cuando tienes dos tribunales con el mismo ID pero diferentes nombres, el sistema NO PUEDE distinguir entre ellos porque 
                ambos se guardan como el mismo numero en la base de datos.</p>
                <p class="mb-0"><strong>Ejemplo:</strong> Si cambias de "Tribunal 64 - Nombre A" a "Tribunal 64 - Nombre B", 
                ambos se guardan como "64" en la tabla maestro, por lo que el sistema reporta "no hay cambios".</p>
            </div>
            
            <h6 class="mt-4">Solucion Definitiva (Requiere limpieza de base de datos):</h6>
            <ol>
                <li><strong>Identificar tribunales duplicados</strong> usando la tabla de arriba</li>
                <li><strong>Decidir cual tribunal es el correcto</strong> para cada ID duplicado</li>
                <li><strong>Renumerar o eliminar</strong> los tribunales duplicados</li>
            </ol>
            
            <h6 class="mt-4">Opcion A: Eliminar Tribunal Duplicado (Recomendado si no se usa)</h6>
            <pre class="bg-light p-3 rounded"><code>-- Paso 1: Verificar que expedientes usan cada tribunal duplicado
SELECT m.n_expediente, m.id_tribunal, t.tribunal 
FROM maestro m 
LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal 
WHERE m.id_tribunal = 64;  -- Cambiar 64 por el ID duplicado

-- Paso 2: Si uno de los tribunales NO se usa en ningun expediente, eliminarlo
DELETE FROM tribunales 
WHERE id_tribunal = 64 AND tribunal = 'Nombre del tribunal a eliminar'
LIMIT 1;</code></pre>
            
            <h6 class="mt-4">Opcion B: Renumerar Tribunal Duplicado (Si ambos se usan)</h6>
            <pre class="bg-light p-3 rounded"><code>-- Paso 1: Encontrar el proximo ID disponible
SELECT MAX(id_tribunal) + 1 as nuevo_id FROM tribunales;

-- Paso 2: Actualizar el tribunal duplicado con un nuevo ID
-- IMPORTANTE: Esto requiere actualizar PRIMERO los expedientes que lo usan
UPDATE maestro 
SET id_tribunal = 999  -- Usar el nuevo ID encontrado
WHERE id_tribunal = 64;  -- Solo si quieres cambiar TODOS los expedientes

-- Paso 3: Actualizar el tribunal en la tabla tribunales
UPDATE tribunales 
SET id_tribunal = 999 
WHERE id_tribunal = 64 AND tribunal = 'Nombre del tribunal a renumerar'
LIMIT 1;</code></pre>
            
            <h6 class="mt-4">Opcion C: Solucion Temporal (Sin tocar la base de datos)</h6>
            <div class="alert alert-warning">
                <p><strong> LIMITACION:</strong> Esta solucion NO resuelve el problema de fondo. El sistema seguira sin poder 
                distinguir entre tribunales con el mismo ID porque la tabla maestro solo guarda numeros.</p>
                <p class="mb-0">El codigo ya fue actualizado para mostrar el nombre del tribunal actual y detectar cambios, 
                pero si seleccionas otro tribunal con el mismo ID, la base de datos seguira guardando el mismo numero.</p>
            </div>
            
            <div class="alert alert-info mt-3">
                <h6><i class="bi bi-lightbulb me-2"></i>Recomendacion del Sistema</h6>
                <p>Para evitar este problema en el futuro:</p>
                <ol class="mb-0">
                    <li>Limpia los IDs duplicados usando las opciones A o B</li>
                    <li>Agrega una restriccion UNIQUE al campo <code>id_tribunal</code> en la tabla <code>tribunales</code></li>
                    <li>Considera usar un ID autoincremental en lugar de IDs manuales</li>
                </ol>
            </div>
            
            <h6 class="mt-4">SQL para prevenir duplicados futuros:</h6>
            <pre class="bg-light p-3 rounded"><code>-- Despues de limpiar los duplicados, ejecutar:
ALTER TABLE tribunales 
ADD UNIQUE KEY unique_id_tribunal (id_tribunal);</code></pre>
            
            <div class="alert alert-danger mt-3">
                <strong> IMPORTANTE:</strong> Antes de ejecutar cualquier comando SQL, haz un respaldo completo de la base de datos 
                usando el modulo de Respaldo Total del sistema. No ejecutes estos comandos sin antes verificar que expedientes 
                se veran afectados.
            </div>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





