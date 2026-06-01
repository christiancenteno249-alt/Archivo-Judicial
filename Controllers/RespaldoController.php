<?php
/**
 * Controllers/RespaldoController.php
 * Controlador para la generación y descarga de copias de seguridad de la base de datos (Solo Admin).
 */
class RespaldoController extends Controller {

    private string $dbHost = 'localhost';
    private string $dbName = 'archivo_judicial';

    /**
     * Muestra la interfaz con las opciones de respaldo.
     */
    public function index(): void {
        $this->requireAdmin();
        
        $host = $this->dbHost;
        $dbname = $this->dbName;
        
        $this->render('respaldo/index', compact('host', 'dbname'));
    }

    /**
     * Procesa la descarga y streaming del respaldo seleccionado.
     */
    public function descargar(): void {
        $this->requireAdmin();

        $formato = $_GET['generar'] ?? '';

        if ($formato === 'sql') {
            try {
                $nombreArchivo = 'respaldo_total_' . date('d_m_Y_His') . '.sql';
                $contenido = $this->generarRespaldoCompleto();
                
                // Auditoría
                $this->auditoria(
                    'RESPALDO_BASE_DATOS', 
                    'Base de Datos Completa (SQL)', 
                    "El Administrador generó una copia de seguridad total del sistema en formato SQL. Archivo: {$nombreArchivo}"
                );

                // Configurar headers para descarga
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
                header('Content-Length: ' . strlen($contenido));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');

                echo $contenido;
                exit;

            } catch (Exception $e) {
                die("Error al generar el respaldo SQL: " . $e->getMessage());
            }
        }

        if ($formato === 'excel') {
            try {
                $nombreArchivo = 'respaldo_total_' . date('d_m_Y_His') . '.csv';
                $contenido = $this->generarRespaldoExcel();
                
                // Auditoría
                $this->auditoria(
                    'RESPALDO_BASE_DATOS', 
                    'Base de Datos Completa (Excel/CSV)', 
                    "El Administrador generó una copia de seguridad total del sistema en formato Excel/CSV. Archivo: {$nombreArchivo}"
                );

                // Configurar headers para descarga CSV (compatible con Excel)
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
                header('Content-Length: ' . strlen($contenido));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');

                echo $contenido;
                exit;

            } catch (Exception $e) {
                die("Error al generar el respaldo CSV/Excel: " . $e->getMessage());
            }
        }

        // Si no es un formato válido, redirigir a la página de inicio
        $this->redirect('/respaldo');
    }

    /**
     * Genera el respaldo total de la estructura y datos de la base de datos en SQL.
     */
    private function generarRespaldoCompleto(): string {
        $respaldo = "";
        $respaldo .= "-- =====================================================\n";
        $respaldo .= "-- RESPALDO COMPLETO DE BASE DE DATOS (MVC SYSTEM)\n";
        $respaldo .= "-- Base de Datos: {$this->dbName}\n";
        $respaldo .= "-- Fecha de Generación: " . date('d/m/Y H:i:s') . "\n";
        $respaldo .= "-- =====================================================\n\n";
        $respaldo .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $respaldo .= "SET time_zone = \"+00:00\";\n\n";

        try {
            // Obtener todas las tablas
            $stmtTablas = $this->db->query("SHOW TABLES");
            $tablas = $stmtTablas->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tablas as $tabla) {
                $respaldo .= "\n-- =====================================================\n";
                $respaldo .= "-- Tabla: {$tabla}\n";
                $respaldo .= "-- =====================================================\n\n";
                $respaldo .= "DROP TABLE IF EXISTS `{$tabla}`;\n\n";

                // Estructura
                $stmtCreate = $this->db->query("SHOW CREATE TABLE `{$tabla}`");
                $createTable = $stmtCreate->fetch(PDO::FETCH_ASSOC);
                $respaldo .= $createTable['Create Table'] . ";\n\n";

                // Datos
                $stmtDatos = $this->db->query("SELECT * FROM `{$tabla}`");
                $datos = $stmtDatos->fetchAll(PDO::FETCH_ASSOC);

                if (count($datos) > 0) {
                    $respaldo .= "-- Datos de la tabla `{$tabla}`\n";
                    foreach ($datos as $fila) {
                        $columnas = array_keys($fila);
                        $valores = array_values($fila);

                        $valoresEscapados = array_map(function($valor) {
                            if ($valor === null) {
                                return 'NULL';
                            }
                            return $this->db->quote($valor);
                        }, $valores);

                        $columnasStr = '`' . implode('`, `', $columnas) . '`';
                        $valoresStr = implode(', ', $valoresEscapados);

                        $respaldo .= "INSERT INTO `{$tabla}` ({$columnasStr}) VALUES ({$valoresStr});\n";
                    }
                    $respaldo .= "\n";
                } else {
                    $respaldo .= "-- La tabla `{$tabla}` está vacía\n\n";
                }
            }

            $respaldo .= "\n-- =====================================================\n";
            $respaldo .= "-- FIN DEL RESPALDO\n";
            $respaldo .= "-- =====================================================\n";

            return $respaldo;

        } catch (PDOException $e) {
            throw new Exception("Error al generar respaldo SQL: " . $e->getMessage());
        }
    }

    /**
     * Genera el respaldo total de los datos en formato CSV estructurado (UTF-8 BOM).
     */
    private function generarRespaldoExcel(): string {
        try {
            $stmtTablas = $this->db->query("SHOW TABLES");
            $tablas = $stmtTablas->fetchAll(PDO::FETCH_COLUMN);

            $contenido = "\xEF\xBB\xBF"; // UTF-8 BOM
            $contenido .= "sep=," . PHP_EOL;

            foreach ($tablas as $tabla) {
                $contenido .= PHP_EOL . "TABLA: " . strtoupper($tabla) . PHP_EOL;
                $contenido .= str_repeat("=", 100) . PHP_EOL . PHP_EOL;

                $stmtDatos = $this->db->query("SELECT * FROM `{$tabla}`");
                $datos = $stmtDatos->fetchAll(PDO::FETCH_ASSOC);

                if (count($datos) > 0) {
                    // Encabezados
                    $columnas = array_keys($datos[0]);
                    $columnasEscapadas = array_map(function($col) {
                        return '"' . str_replace('"', '""', $col) . '"';
                    }, $columnas);
                    $contenido .= implode(",", $columnasEscapadas) . PHP_EOL;

                    // Filas
                    foreach ($datos as $fila) {
                        $valores = array_map(function($valor) {
                            if ($valor === null) return '';
                            $valor = str_replace('"', '""', $valor);
                            $valor = str_replace(["\n", "\r"], [" ", ""], $valor);
                            return '"' . $valor . '"';
                        }, array_values($fila));
                        $contenido .= implode(",", $valores) . PHP_EOL;
                    }
                } else {
                    $contenido .= "(Tabla vacía)" . PHP_EOL;
                }
                $contenido .= PHP_EOL . PHP_EOL;
            }

            return $contenido;

        } catch (PDOException $e) {
            throw new Exception("Error al generar respaldo Excel: " . $e->getMessage());
        }
    }
}
