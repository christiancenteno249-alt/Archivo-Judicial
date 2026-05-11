# Filtros Avanzados de Auditoría en el Buscador

## Resumen

Se han implementado filtros avanzados en el buscador para convertirlo en una herramienta de auditoría real, permitiendo búsquedas por rangos de fechas y filtrado de expedientes con movimientos registrados.

---

## Nuevas Funcionalidades

### 1. Filtro de Rango de Fechas

**Campos agregados:**
- **Fecha Desde:** Inicio del rango de búsqueda
- **Fecha Hasta:** Fin del rango de búsqueda

**Comportamiento:**
- ✅ Si ambos campos tienen valor: Busca expedientes entre esas fechas (BETWEEN)
- ✅ Si solo "Fecha Desde" tiene valor: Busca desde esa fecha hasta hoy
- ✅ Si solo "Fecha Hasta" tiene valor: Busca hasta esa fecha
- ✅ Formato: YYYY-MM-DD (compatible con MySQL/MariaDB)

**Lógica SQL:**
```sql
-- Ambas fechas
WHERE DATE(m.fecha_entrada) BETWEEN '2024-01-01' AND '2024-12-31'

-- Solo fecha_desde
WHERE DATE(m.fecha_entrada) >= '2024-01-01'

-- Solo fecha_hasta
WHERE DATE(m.fecha_entrada) <= '2024-12-31'
```

---

### 2. Filtro de Movimientos Recientes

**Campo agregado:**
- **Checkbox:** "Solo expedientes con movimientos registrados"

**Comportamiento:**
- ✅ Cuando está **marcado**: Muestra solo expedientes que tienen historial de movimientos
- ✅ Cuando está **desmarcado**: Muestra todos los expedientes (comportamiento normal)

**Lógica SQL:**
```sql
-- Con movimientos (checkbox marcado)
FROM maestro m 
INNER JOIN historial_movimientos hm ON m.n_expediente = hm.n_expediente
LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal

-- Sin filtro (checkbox desmarcado)
FROM maestro m 
LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal
```

**Optimización:**
- Se usa `GROUP BY m.Id` para evitar duplicados cuando hay múltiples movimientos
- El COUNT usa `COUNT(DISTINCT m.Id)` para contar expedientes únicos

---

## Interfaz de Usuario

### Ubicación de los Filtros

Los nuevos filtros están ubicados en una sección separada del formulario:

```
┌─────────────────────────────────────────────┐
│ N° Expediente  │ N° Legajo  │ Demandante   │
│ CI Demandante  │ Demandado  │ CI Demandado │
│ Fecha Entrada                               │
├─────────────────────────────────────────────┤
│ ═══ Filtros Avanzados de Auditoría ═══     │
│ Fecha Desde    │ Fecha Hasta               │
│ ☐ Solo expedientes con movimientos         │
└─────────────────────────────────────────────┘
```

### Diseño Visual

- **Separador:** Línea horizontal (hr) antes de los filtros avanzados
- **Título:** "Filtros Avanzados de Auditoría" con icono de calendario
- **Checkbox:** Con icono de flechas y texto descriptivo
- **Ayuda:** Texto pequeño explicando el filtro de movimientos

---

## Casos de Uso

### Caso 1: Auditoría Mensual
**Objetivo:** Ver todos los expedientes ingresados en enero 2024

**Pasos:**
1. Fecha Desde: `2024-01-01`
2. Fecha Hasta: `2024-01-31`
3. Ejecutar Búsqueda

**Resultado:** Lista de expedientes ingresados en enero 2024

---

### Caso 2: Expedientes Activos
**Objetivo:** Ver expedientes con movimientos recientes

**Pasos:**
1. Marcar checkbox "Solo expedientes con movimientos registrados"
2. Ejecutar Búsqueda

**Resultado:** Solo expedientes que tienen historial de movimientos

---

### Caso 3: Auditoría Trimestral con Movimientos
**Objetivo:** Ver expedientes del primer trimestre que tienen movimientos

**Pasos:**
1. Fecha Desde: `2024-01-01`
2. Fecha Hasta: `2024-03-31`
3. Marcar checkbox "Solo expedientes con movimientos registrados"
4. Ejecutar Búsqueda

**Resultado:** Expedientes del Q1 2024 con historial de movimientos

---

### Caso 4: Expedientes Desde una Fecha
**Objetivo:** Ver todos los expedientes desde junio 2024 hasta hoy

**Pasos:**
1. Fecha Desde: `2024-06-01`
2. Fecha Hasta: (dejar vacío)
3. Ejecutar Búsqueda

**Resultado:** Expedientes desde junio 2024 hasta la fecha actual

---

## Lógica de Blindaje

### Validación de Fechas

```php
// Si fecha_desde tiene valor pero fecha_hasta no, asumir fecha_hasta = hoy
if (!empty($fecha_desde) && empty($fecha_hasta)) {
    $fecha_hasta = date('Y-m-d');
}
```

**Ventaja:** El usuario no necesita llenar ambos campos si quiere buscar "desde X hasta hoy"

---

### Prevención de Duplicados

```sql
-- COUNT con DISTINCT
SELECT COUNT(DISTINCT m.Id) as total FROM ...

-- SELECT con GROUP BY
SELECT m.*, t.tribunal FROM ... GROUP BY m.Id
```

**Ventaja:** Aunque un expediente tenga múltiples movimientos, solo aparece una vez en los resultados

---

## Optimización de Consultas

### Índices Recomendados

Para mejorar el rendimiento de las búsquedas por fecha:

```sql
-- Índice en fecha_entrada
CREATE INDEX idx_fecha_entrada ON maestro(fecha_entrada);

-- Índice en n_expediente para el JOIN
CREATE INDEX idx_n_expediente ON historial_movimientos(n_expediente);
```

---

## Compatibilidad

### Formato de Fechas

- **Input HTML:** `<input type="date">` genera formato `YYYY-MM-DD`
- **MySQL/MariaDB:** Acepta formato `YYYY-MM-DD` nativamente
- **PHP:** `date('Y-m-d')` genera formato compatible

**Resultado:** No se necesita conversión de formatos

---

## Variables Capturadas

```php
$fecha_desde = trim($_GET['fecha_desde'] ?? '');
$fecha_hasta = trim($_GET['fecha_hasta'] ?? '');
$con_movimientos = isset($_GET['con_movimientos']) && $_GET['con_movimientos'] == '1';
```

---

## Parámetros SQL

```php
$parametros[':fecha_desde'] = $fecha_desde;
$parametros[':fecha_hasta'] = $fecha_hasta;
```

**Seguridad:** Se usan prepared statements (PDO) para prevenir SQL injection

---

## Integración con Paginación

Los nuevos filtros se integran perfectamente con la paginación existente:

```php
function getQueryParams($exclude = []) {
    $params = $_GET;
    foreach ($exclude as $key) {
        unset($params[$key]);
    }
    return http_build_query($params);
}
```

**Resultado:** Al cambiar de página, los filtros se mantienen activos

---

## Mensajes de Búsqueda

El sistema actualiza automáticamente el mensaje de resultados:

```php
$hay_busqueda = $expediente !== '' || $n_legajo !== '' || $demandante !== '' || 
                $ced_dante !== '' || $demandado !== '' || $ced_dado !== '' || 
                $fecha !== '' || $fecha_desde !== '' || $fecha_hasta !== '' || 
                $con_movimientos;
```

---

## Ventajas de Esta Implementación

1. ✅ **Auditoría Real:** Permite análisis por períodos específicos
2. ✅ **Flexible:** Funciona con uno o ambos campos de fecha
3. ✅ **Inteligente:** Auto-completa fecha_hasta si falta
4. ✅ **Optimizado:** GROUP BY previene duplicados
5. ✅ **Seguro:** Prepared statements previenen SQL injection
6. ✅ **Compatible:** Formato de fecha estándar MySQL
7. ✅ **Integrado:** Funciona con paginación y otros filtros
8. ✅ **Intuitivo:** Interfaz clara con ayuda contextual

---

## Pruebas Recomendadas

1. ✅ Buscar por rango de fechas (ambos campos)
2. ✅ Buscar solo con fecha_desde (debe asumir hasta hoy)
3. ✅ Buscar solo con fecha_hasta
4. ✅ Activar filtro de movimientos solo
5. ✅ Combinar rango de fechas + filtro de movimientos
6. ✅ Verificar que no haya duplicados en resultados
7. ✅ Verificar que la paginación mantenga los filtros
8. ✅ Probar con expedientes sin movimientos

---

## Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `buscador.php` | Agregados filtros de fecha y movimientos |
| `FILTROS_AUDITORIA_BUSCADOR.md` | Esta documentación |

---

## Próximas Mejoras Sugeridas

1. **Filtro por Tribunal:** Dropdown para filtrar por tribunal específico
2. **Filtro por Usuario:** Ver expedientes registrados por un usuario
3. **Exportar Resultados:** Botón para exportar búsqueda a Excel/PDF
4. **Estadísticas:** Mostrar resumen de resultados (total por mes, etc.)
5. **Búsqueda Guardada:** Permitir guardar filtros frecuentes

---

## Soporte

Si necesitas ayuda con los filtros:
1. Revisa este documento
2. Verifica que las fechas estén en formato correcto (YYYY-MM-DD)
3. Comprueba que el checkbox esté marcado si quieres filtrar por movimientos
4. Consulta los casos de uso para ejemplos prácticos
