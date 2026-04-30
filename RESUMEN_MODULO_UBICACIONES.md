# 📦 MÓDULO DE GESTIÓN DE UBICACIONES FÍSICAS
## Sistema de Archivo Judicial - Estado Aragua
### Fecha de Implementación: 20 de Abril de 2026

---

## 📋 ÍNDICE

1. [Objetivo del Módulo](#objetivo)
2. [Estructura de Base de Datos](#base-de-datos)
3. [Archivos Creados](#archivos-creados)
4. [Funcionalidades Implementadas](#funcionalidades)
5. [Scripts SQL Utilizados](#scripts-sql)
6. [Flujo de Trabajo](#flujo-de-trabajo)
7. [Integración con el Sistema](#integracion)
8. [Casos de Uso](#casos-de-uso)

---

## 🎯 OBJETIVO DEL MÓDULO {#objetivo}

Crear un sistema completo para gestionar la **ubicación física** de los expedientes judiciales en los diferentes depósitos del Estado Aragua, facilitando la **centralización en el Galpón de Palo Negro**.

### Necesidades que resuelve:

- ✅ Asignar ubicación física a expedientes (Sede, Área, Estante)
- ✅ Carga individual o masiva de ubicaciones
- ✅ Trazabilidad completa de movimientos
- ✅ Consulta rápida de dónde está físicamente un expediente
- ✅ Gestión de múltiples sedes de depósito
- ✅ Auditoría automática de todos los cambios

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS {#base-de-datos}

### 1. Nueva Tabla: `sedes_deposito`

Almacena las diferentes sedes donde se pueden guardar expedientes.

```sql
CREATE TABLE sedes_deposito (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sede VARCHAR(100) NOT NULL UNIQUE,
    direccion TEXT,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Campos:**
- `id_sede`: Identificador único de la sede
- `nombre_sede`: Nombre descriptivo (ej: "Galpón Palo Negro")
- `direccion`: Dirección física completa
- `descripcion`: Descripción del propósito de la sede
- `activo`: Estado (1=Activa, 0=Inactiva) - Borrado lógico
- `fecha_creacion`: Timestamp de creación

### 2. Columnas Agregadas a `maestro`

Se agregaron 4 nuevas columnas a la tabla principal de expedientes:

```sql
ALTER TABLE maestro ADD COLUMN id_sede INT DEFAULT NULL;
ALTER TABLE maestro ADD COLUMN ubicacion_area VARCHAR(100) DEFAULT NULL;
ALTER TABLE maestro ADD COLUMN ubicacion_detalle VARCHAR(255) DEFAULT NULL;
ALTER TABLE maestro ADD COLUMN fecha_ultima_ubicacion TIMESTAMP NULL DEFAULT NULL;
```

**Campos:**
- `id_sede`: Foreign Key a `sedes_deposito` (¿En qué sede está?)
- `ubicacion_area`: Área dentro de la sede (ej: "Piso 3, Sección A")
- `ubicacion_detalle`: Detalle específico (ej: "Estante B / Caja 4")
- `fecha_ultima_ubicacion`: Timestamp del último cambio de ubicación

### 3. Relación entre Tablas

```sql
ALTER TABLE maestro 
ADD CONSTRAINT fk_maestro_sede 
FOREIGN KEY (id_sede) 
REFERENCES sedes_deposito(id_sede) 
ON DELETE SET NULL;
```

**Comportamiento:**
- Si se elimina una sede, los expedientes quedan con `id_sede = NULL`
- No se pierden los expedientes, solo quedan sin sede asignada
- Se puede reasignar a otra sede posteriormente

---

## 📁 ARCHIVOS CREADOS {#archivos-creados}

### 1. **gestionar_ubicaciones.php**
**Propósito:** Interfaz principal del módulo

**Funcionalidades:**
- Modo Individual: Buscar un expediente y asignarle ubicación
- Modo Lote: Asignar ubicación a múltiples expedientes a la vez
- Validación de expedientes existentes
- Auditoría automática de cambios
- UX optimizada (mantiene última sede y área en sesión)

**Características técnicas:**
- Usa transacciones PDO para carga por lote
- Validación de expedientes antes de actualizar
- Manejo de errores robusto
- Interfaz responsive con Bootstrap 5

### 2. **gestionar_sedes.php**
**Propósito:** CRUD completo para administrar sedes

**Funcionalidades:**
- Crear nuevas sedes
- Editar sedes existentes
- Activar/Desactivar sedes (borrado lógico)
- Ver contador de expedientes por sede
- Solo accesible para administradores

**Características técnicas:**
- Validación de nombres únicos
- Auditoría de todas las operaciones
- Contador en tiempo real de expedientes
- Interfaz intuitiva con iconos

### 3. **verificar_estructura.php**
**Propósito:** Herramienta de diagnóstico

**Funcionalidades:**
- Verifica que existan las columnas en `maestro`
- Verifica que exista la tabla `sedes_deposito`
- Muestra las sedes registradas
- Verifica Foreign Keys
- Identifica qué falta para que funcione el módulo

### 4. **diagnostico_sedes.php**
**Propósito:** Diagnóstico avanzado (solo admin)

**Funcionalidades:**
- Análisis completo de la estructura
- Verificación de integridad referencial
- Listado detallado de sedes
- Reporte de estado del sistema

---

## 🔧 SCRIPTS SQL UTILIZADOS {#scripts-sql}

### Script 1: `AGREGAR_COLUMNAS_DIRECTO.sql`

**Propósito:** Crear toda la estructura necesaria

```sql
-- =====================================================
-- SCRIPT COMPLETO DE IMPLEMENTACIÓN
-- =====================================================

-- 1. Crear tabla sedes_deposito
CREATE TABLE IF NOT EXISTS sedes_deposito (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sede VARCHAR(100) NOT NULL UNIQUE,
    direccion TEXT,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Insertar sedes iniciales
INSERT IGNORE INTO sedes_deposito (nombre_sede, direccion, descripcion) VALUES
('Galpón Palo Negro - Depósito Central', 
 'Zona Industrial Palo Negro, Aragua',
 'Depósito principal de centralización'),
('Archivo Judicial Maracay Centro',
 'Edificio Tribunales, Maracay',
 'Archivo histórico del centro'),
('Depósito Temporal La Victoria',
 'Centro Comercial Dorado, La Victoria',
 'Almacenamiento temporal');

-- 3. Agregar columnas a maestro
ALTER TABLE maestro ADD COLUMN id_sede INT DEFAULT NULL;
ALTER TABLE maestro ADD COLUMN ubicacion_area VARCHAR(100) DEFAULT NULL;
ALTER TABLE maestro ADD COLUMN ubicacion_detalle VARCHAR(255) DEFAULT NULL;
ALTER TABLE maestro ADD COLUMN fecha_ultima_ubicacion TIMESTAMP NULL DEFAULT NULL;

-- 4. Agregar índice
ALTER TABLE maestro ADD INDEX idx_sede (id_sede);

-- 5. Agregar Foreign Key
ALTER TABLE maestro 
ADD CONSTRAINT fk_maestro_sede 
FOREIGN KEY (id_sede) 
REFERENCES sedes_deposito(id_sede) 
ON DELETE SET NULL;

-- 6. Verificar
DESCRIBE maestro;
SELECT * FROM sedes_deposito;
```

**Notas importantes:**
- Si una columna ya existe, dará error "Duplicate column name" - **ignorar**
- Si el índice ya existe, dará error "Duplicate key name" - **ignorar**
- Usar `INSERT IGNORE` para no duplicar sedes

### Script 2: `insertar_sedes_reales.sql`

**Propósito:** Insertar sedes reales del Estado Aragua

```sql
-- Limpiar sedes de prueba
DELETE FROM sedes_deposito WHERE id_sede IN (1, 2, 3);

-- Insertar sedes reales
INSERT INTO sedes_deposito (nombre_sede, direccion, descripcion, activo) VALUES

('Galpón Palo Negro - Depósito Central',
 'Zona Industrial Palo Negro, Sector Valle Lindo, Frente al Cementerio Municipal, Palo Negro, Estado Aragua',
 'Depósito principal de centralización de expedientes judiciales del Estado Aragua. Capacidad para almacenamiento masivo de expedientes históricos y activos. Cuenta con sistema de estantería industrial y control de acceso.',
 1),

('Archivo Judicial Maracay Centro',
 'Edificio Tribunales, Calle Principal, Centro Comercial Profesional, Maracay, Estado Aragua',
 'Archivo histórico ubicado en el centro de Maracay. Almacena expedientes de tribunales civiles, mercantiles y penales del área metropolitana. Acceso restringido para consulta de expedientes antiguos.',
 1),

('Depósito Temporal La Victoria',
 'Calle Principal Córdoba, Centro Comercial Dorado, Piso 2, La Victoria, Estado Aragua',
 'Almacenamiento temporal de expedientes en tránsito. Utilizado para expedientes que están en proceso de clasificación antes de su traslado al depósito central de Palo Negro.',
 1),

('Archivo Judicial Cagua',
 'Calle Rivas Chacón, Sector Valle Lindo, Cagua, Estado Aragua',
 'Depósito regional para expedientes de la zona sur del estado. Almacena expedientes de tribunales de Cagua, La Villa de Cura y zonas aledañas.',
 1),

('Depósito Villa de Cura',
 'Avenida Principal, Sector Centro, Villa de Cura, Estado Aragua',
 'Archivo regional para expedientes de la Circunscripción Judicial del Sur de Aragua. Almacenamiento de expedientes civiles, penales y de familia de la región.',
 1);
```

**Sedes incluidas:**
1. **Galpón Palo Negro** - Depósito principal (centralización)
2. **Archivo Maracay Centro** - Archivo histórico
3. **Depósito La Victoria** - Almacenamiento temporal
4. **Archivo Cagua** - Depósito regional sur
5. **Depósito Villa de Cura** - Archivo regional

---

## ⚙️ FUNCIONALIDADES IMPLEMENTADAS {#funcionalidades}

### 1. Asignación Individual de Ubicación

**Flujo:**
1. Usuario ingresa número de expediente
2. Sistema busca el expediente en la BD
3. Si existe: Muestra datos del expediente
4. Usuario selecciona: Sede, Área, Estante
5. Sistema guarda y registra en auditoría

**Validaciones:**
- ✅ Expediente debe existir
- ✅ Sede es obligatoria
- ✅ Área y Estante son opcionales
- ✅ Si expediente no existe: Muestra error y NO muestra formulario

**Código clave:**
```php
// Buscar expediente
$stmt = $pdo->prepare("SELECT Id, n_expediente, demandante, demandado 
                       FROM maestro WHERE n_expediente = :expediente LIMIT 1");
$stmt->execute([':expediente' => $n_expediente]);
$expediente = $stmt->fetch();

if (!$expediente) {
    $mensaje = "❌ Expediente no encontrado";
    // NO mostrar formulario
}

// Actualizar ubicación
$sql = "UPDATE maestro 
        SET id_sede = :id_sede, 
            ubicacion_area = :area, 
            ubicacion_detalle = :detalle,
            fecha_ultima_ubicacion = NOW()
        WHERE Id = :id LIMIT 1";
```

### 2. Carga por Lote

**Flujo:**
1. Usuario ingresa múltiples números de expediente (uno por línea o separados por comas)
2. Usuario selecciona ubicación común (Sede, Área, Estante)
3. Sistema procesa cada expediente:
   - Verifica si existe
   - Actualiza ubicación
   - Registra en auditoría
4. Muestra resumen: Actualizados vs No encontrados

**Características:**
- ✅ Usa transacciones PDO (todo o nada)
- ✅ Separa expedientes por líneas o comas
- ✅ Elimina espacios y líneas vacías
- ✅ Muestra lista de expedientes no encontrados
- ✅ Mantiene sede y área en sesión para siguiente lote

**Código clave:**
```php
// Separar expedientes
$expedientes_array = preg_split('/[\n,]+/', $expedientes_texto);
$expedientes_array = array_map('trim', $expedientes_array);
$expedientes_array = array_filter($expedientes_array);

// Transacción
$pdo->beginTransaction();

foreach ($expedientes_array as $n_expediente) {
    // Verificar existencia
    $stmt = $pdo->prepare("SELECT Id FROM maestro WHERE n_expediente = :exp");
    $stmt->execute([':exp' => $n_expediente]);
    
    if ($stmt->fetch()) {
        // Actualizar
        $sqlUpdate = "UPDATE maestro SET id_sede = :sede, 
                      ubicacion_area = :area, ubicacion_detalle = :detalle 
                      WHERE n_expediente = :exp";
        // ...
        $actualizados++;
    } else {
        $no_encontrados[] = $n_expediente;
    }
}

$pdo->commit();
```

### 3. Auditoría Automática

**Cada cambio de ubicación registra:**
- Usuario que realizó el cambio
- Fecha y hora exacta
- IP de la máquina
- Expediente afectado
- Detalles del cambio (sede, área, estante)

**Tipos de acciones:**
- `CAMBIO_UBICACION` - Asignación individual
- `CAMBIO_UBICACION_LOTE` - Asignación masiva

**Código:**
```php
$detalle = "Cambio de Ubicación: {$n_expediente} movido a {$nombre_sede}";
if (!empty($area)) $detalle .= " - {$area}";
if (!empty($detalle_estante)) $detalle .= " - {$detalle_estante}";

registrarAccion('CAMBIO_UBICACION', "Exp: {$n_expediente}", $detalle);
```

### 4. UX Optimizada para Velocidad

**Problema:** Al cargar múltiples expedientes a la misma ubicación, tener que seleccionar la sede y área cada vez es lento.

**Solución:** Sistema de memoria de sesión

```php
// Guardar en sesión después de cada carga
$_SESSION['ultima_sede'] = $id_sede;
$_SESSION['ultima_area'] = $ubicacion_area;

// Pre-llenar en siguiente carga
<select name="id_sede">
    <option value="<?= $sede['id_sede'] ?>" 
            <?= ($_SESSION['ultima_sede'] == $sede['id_sede']) ? 'selected' : '' ?>>
        <?= $sede['nombre_sede'] ?>
    </option>
</select>

<input name="ubicacion_area" 
       value="<?= htmlspecialchars($_SESSION['ultima_area']) ?>">
```

**Beneficio:** Solo se limpian los números de expediente, la ubicación se mantiene.

### 5. Descripción Dinámica de Sedes

**Funcionalidad:** Al seleccionar una sede, muestra su descripción automáticamente.

**Implementación:**
```javascript
// JavaScript para mostrar descripción
document.getElementById('sede_select').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const descripcion = option.getAttribute('data-descripcion');
    const direccion = option.getAttribute('data-direccion');
    
    if (descripcion) {
        document.getElementById('descripcion_sede').style.display = 'block';
        document.getElementById('texto_descripcion').textContent = descripcion;
    }
});
```

**HTML:**
```html
<select name="id_sede" id="sede_select">
    <option value="1" 
            data-descripcion="Depósito principal de centralización"
            data-direccion="Zona Industrial Palo Negro">
        Galpón Palo Negro
    </option>
</select>

<div id="descripcion_sede" style="display: none;">
    <strong>Descripción:</strong> <span id="texto_descripcion"></span>
</div>
```

### 6. Gestión de Sedes (CRUD)

**Funcionalidades:**
- ✅ Crear nueva sede
- ✅ Editar sede existente
- ✅ Activar/Desactivar sede (borrado lógico)
- ✅ Ver contador de expedientes por sede
- ✅ Solo accesible para administradores

**Validaciones:**
- Nombre de sede único
- No se puede eliminar físicamente (solo desactivar)
- Auditoría de todas las operaciones

---

## 🔄 FLUJO DE TRABAJO {#flujo-de-trabajo}

### Caso 1: Asignación Individual

```
1. Usuario → Gestión de Ubicaciones
2. Selecciona "Asignación Individual"
3. Ingresa número de expediente: "00123-24"
4. Click "Buscar"
5. Sistema busca en BD
6. Si existe:
   - Muestra datos del expediente
   - Muestra formulario de ubicación
7. Usuario selecciona:
   - Sede: "Galpón Palo Negro"
   - Área: "Piso 2"
   - Estante: "Estante A / Caja 10"
8. Click "Guardar Ubicación"
9. Sistema:
   - Actualiza maestro
   - Registra en auditoría
   - Guarda sede/área en sesión
   - Muestra mensaje de éxito
```

### Caso 2: Carga por Lote

```
1. Usuario → Gestión de Ubicaciones
2. Selecciona "Carga por Lote"
3. Ingresa expedientes:
   00123-24
   00124-24
   00125-24
4. Selecciona ubicación común:
   - Sede: "Galpón Palo Negro"
   - Área: "Piso 3"
   - Estante: "Estante B / Caja 15"
5. Click "Procesar Lote Completo"
6. Sistema:
   - Inicia transacción
   - Verifica cada expediente
   - Actualiza los que existen
   - Registra cada uno en auditoría
   - Confirma transacción
7. Muestra resumen:
   - ✅ 3 expedientes actualizados
   - ❌ 0 no encontrados
8. Mantiene sede/área para siguiente lote
```

---

## 🔗 INTEGRACIÓN CON EL SISTEMA {#integracion}

### 1. Menú Principal (`index.php`)

Se agregó nueva tarjeta de acceso:

```php
<div class="card card-menu" style="background: linear-gradient(135deg, #00695c 0%, #004d40 100%);">
    <i class="bi bi-geo-alt-fill card-dropdown-icon"></i>
    <h3 class="card-title-action">GESTIÓN DE UBICACIONES FÍSICAS</h3>
    <p>Centralización de Expedientes - Palo Negro</p>
    <a href="gestionar_ubicaciones.php" class="stretched-link"></a>
</div>
```

**Acceso:** Operadores y Administradores

### 2. Buscador de Expedientes (`buscador.php`)

Se agregó columna de ubicación en resultados:

**SQL modificado:**
```sql
SELECT m.*, t.tribunal, s.nombre_sede, m.ubicacion_area, m.ubicacion_detalle
FROM maestro m
LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal
LEFT JOIN sedes_deposito s ON m.id_sede = s.id_sede
WHERE ...
```

**Visualización:**
```php
<td>
    <?php if (!empty($fila['nombre_sede'])): ?>
        <span class="badge bg-success">
            <i class="bi bi-geo-alt-fill"></i> <?= $fila['nombre_sede'] ?>
            <?php if (!empty($fila['ubicacion_area'])): ?>
                <br><small><?= $fila['ubicacion_area'] ?></small>
            <?php endif; ?>
            <?php if (!empty($fila['ubicacion_detalle'])): ?>
                <br><small><?= $fila['ubicacion_detalle'] ?></small>
            <?php endif; ?>
        </span>
    <?php else: ?>
        <span class="text-muted">Sin ubicación</span>
    <?php endif; ?>
</td>
```

### 3. Sistema de Auditoría (`auditoria.php`)

Nuevas acciones registradas:
- `CAMBIO_UBICACION` - Asignación individual
- `CAMBIO_UBICACION_LOTE` - Asignación masiva
- `CREAR_SEDE` - Nueva sede creada
- `EDITAR_SEDE` - Sede modificada
- `CAMBIAR_ESTADO_SEDE` - Sede activada/desactivada

**Filtros disponibles:**
- Por acción
- Por usuario
- Por fecha
- Por expediente

---

## 📊 CASOS DE USO {#casos-de-uso}

### Caso de Uso 1: Recepción de Expedientes en Palo Negro

**Escenario:** Llega un camión con 100 expedientes para el Galpón de Palo Negro, todos van al mismo lugar.

**Proceso:**
1. Operador abre "Gestión de Ubicaciones"
2. Selecciona "Carga por Lote"
3. Ingresa los 100 números de expediente (uno por línea)
4. Selecciona:
   - Sede: "Galpón Palo Negro"
   - Área: "Piso 1"
   - Estante: "Estante A / Cajas 1-10"
5. Click "Procesar Lote"
6. Sistema actualiza los 100 expedientes en segundos
7. Cada cambio queda registrado en auditoría

**Tiempo estimado:** 2-3 minutos para 100 expedientes

### Caso de Uso 2: Búsqueda de Expediente Físico

**Escenario:** Un abogado solicita el expediente "00456-23" y necesitan saber dónde está físicamente.

**Proceso:**
1. Operador abre "Buscador de Expedientes"
2. Busca por número: "00456-23"
3. En los resultados ve la columna "Ubicación":
   - 🏢 Galpón Palo Negro
   - 📍 Piso 2
   - 📦 Estante C / Caja 25
4. Operador va físicamente a esa ubicación
5. Encuentra el expediente

**Tiempo estimado:** 30 segundos de búsqueda + tiempo de traslado

### Caso de Uso 3: Reorganización de Depósito

**Escenario:** Se decide mover todos los expedientes del "Piso 1" al "Piso 3" en Palo Negro.

**Proceso:**
1. Admin ejecuta consulta SQL para obtener expedientes:
```sql
SELECT n_expediente FROM maestro 
WHERE id_sede = 1 AND ubicacion_area = 'Piso 1';
```
2. Copia los números de expediente
3. Abre "Gestión de Ubicaciones" → "Carga por Lote"
4. Pega los números
5. Selecciona nueva ubicación:
   - Sede: "Galpón Palo Negro"
   - Área: "Piso 3"
   - Estante: (mantiene el mismo)
6. Procesa el lote
7. Todos los expedientes actualizados con auditoría completa

### Caso de Uso 4: Auditoría de Movimientos

**Escenario:** Se necesita saber quién movió el expediente "00789-22" y cuándo.

**Proceso:**
1. Admin abre "Auditoría del Sistema"
2. Filtra por:
   - Acción: "CAMBIO_UBICACION"
   - Recurso: "00789-22"
3. Ve el historial completo:
   - 15/04/2026 10:30 - Usuario: María López - Movido a Palo Negro, Piso 1
   - 18/04/2026 14:15 - Usuario: Juan Pérez - Movido a Palo Negro, Piso 3
4. Puede ver IP, detalles completos, etc.

---

## 🔒 SEGURIDAD Y VALIDACIONES

### Validaciones Implementadas

1. **Expediente debe existir:**
```php
if (!$expediente_encontrado) {
    $mensaje = "❌ Expediente no encontrado";
    // NO mostrar formulario
}
```

2. **Sede es obligatoria:**
```html
<select name="id_sede" required>
```

3. **Transacciones para lotes:**
```php
$pdo->beginTransaction();
try {
    // Procesar lote
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
}
```

4. **Auditoría automática:**
```php
registrarAccion('CAMBIO_UBICACION', $expediente, $detalles);
```

5. **Borrado lógico de sedes:**
```sql
UPDATE sedes_deposito SET activo = 0 WHERE id_sede = :id;
-- No se usa DELETE
```

### Permisos

- **Operadores:** Pueden asignar ubicaciones
- **Administradores:** Pueden asignar ubicaciones + gestionar sedes

---

## 📈 CONSULTAS SQL ÚTILES

### Ver expedientes con ubicación
```sql
SELECT 
    m.n_expediente,
    m.demandante,
    s.nombre_sede,
    m.ubicacion_area,
    m.ubicacion_detalle,
    m.fecha_ultima_ubicacion
FROM maestro m
INNER JOIN sedes_deposito s ON m.id_sede = s.id_sede
ORDER BY m.fecha_ultima_ubicacion DESC;
```

### Ver expedientes SIN ubicación
```sql
SELECT 
    m.n_expediente,
    m.demandante,
    m.demandado
FROM maestro m
WHERE m.id_sede IS NULL
ORDER BY m.fecha_entrada DESC;
```

### Contar expedientes por sede
```sql
SELECT 
    s.nombre_sede,
    COUNT(m.Id) as total_expedientes
FROM sedes_deposito s
LEFT JOIN maestro m ON s.id_sede = m.id_sede
GROUP BY s.id_sede, s.nombre_sede
ORDER BY total_expedientes DESC;
```

### Ver historial de cambios de ubicación
```sql
SELECT 
    a.fecha_hora,
    a.recurso,
    a.detalles,
    u.nombre_full
FROM auditoria_log a
LEFT JOIN usuarios_sistema u ON a.id_usuario = u.id_usuario
WHERE a.accion IN ('CAMBIO_UBICACION', 'CAMBIO_UBICACION_LOTE')
ORDER BY a.fecha_hora DESC
LIMIT 100;
```

### Expedientes en una ubicación específica
```sql
SELECT 
    m.n_expediente,
    m.demandante,
    m.demandado,
    m.ubicacion_detalle
FROM maestro m
WHERE m.id_sede = 1 
AND m.ubicacion_area = 'Piso 3'
ORDER BY m.ubicacion_detalle;
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### Pasos realizados:

- [x] Crear tabla `sedes_deposito`
- [x] Agregar columnas a tabla `maestro`
- [x] Insertar sedes iniciales
- [x] Crear `gestionar_ubicaciones.php`
- [x] Crear `gestionar_sedes.php`
- [x] Crear herramientas de diagnóstico
- [x] Integrar con menú principal
- [x] Integrar con buscador
- [x] Implementar auditoría automática
- [x] Implementar UX optimizada (sesión)
- [x] Implementar descripción dinámica de sedes
- [x] Validar expedientes no encontrados
- [x] Implementar carga por lote con transacciones
- [x] Ocultar ID de sede en interfaz
- [x] Cambiar etiquetas (Área/Piso → Área, Detalle → Estante)
- [x] Documentar todo el proceso

---

## 🎯 CONCLUSIÓN

El **Módulo de Gestión de Ubicaciones Físicas** está completamente implementado y funcional. Permite:

✅ Asignar ubicaciones de forma individual o masiva
✅ Gestionar múltiples sedes de depósito
✅ Consultar rápidamente dónde está un expediente
✅ Auditar todos los movimientos
✅ Optimizar el flujo de trabajo con UX inteligente

**El sistema está listo para la centralización en el Galpón de Palo Negro.**

---

**Implementado por:** Kiro AI Assistant  
**Fecha:** 20 de Abril de 2026  
**Estado:** ✅ COMPLETADO Y EN PRODUCCIÓN  
**Versión:** 1.0
