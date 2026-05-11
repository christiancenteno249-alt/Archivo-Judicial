# Solución al Problema de Tribunales Duplicados

## Problema Identificado

El sistema tiene tribunales con el mismo `id_tribunal` pero diferentes nombres en la tabla `tribunales`. Esto causa que al editar un expediente y cambiar de un tribunal a otro con el mismo ID, el sistema reporte "no hay cambios".

### Causa Raíz

La tabla `maestro` solo almacena el campo `id_tribunal` (numérico), no el nombre del tribunal. Cuando existen dos tribunales con el mismo ID pero diferentes nombres:

- **Tribunal 64 - Nombre A**
- **Tribunal 64 - Nombre B**

Ambos se guardan como "64" en la tabla maestro, por lo que el sistema no puede distinguir entre ellos.

## Cambios Implementados en el Código

### 1. Mejora en `editar_registro.php`

#### Cambio en el Dropdown de Tribunales
- Ahora muestra el nombre del tribunal actual debajo del selector
- La selección se basa en TANTO el ID como el nombre del tribunal
- Agrega un atributo `data-nombre` para identificación adicional

#### Mejora en la Lógica de Comparación
- Detecta cambios comparando TANTO el ID como el nombre del tribunal
- Consulta el nombre real del tribunal desde la base de datos antes y después del cambio
- Registra cambios con nombres completos en el log de auditoría

**Código agregado:**
```php
// Obtener el nombre del tribunal VIEJO y NUEVO para comparación precisa
$tribunal_viejo_nombre = '';
$stmtTribViejo = $pdo->prepare("SELECT tribunal FROM tribunales WHERE id_tribunal = :id LIMIT 1");
$stmtTribViejo->execute([':id' => $datos_viejos['id_tribunal']]);
$tribViejo = $stmtTribViejo->fetch();
if ($tribViejo) {
    $tribunal_viejo_nombre = $tribViejo['tribunal'];
}

// Comparación especial para tribunales
if ($campo === 'id_tribunal') {
    $id_cambio = ((int)$valor_viejo !== (int)$valor_nuevo);
    $nombre_cambio = ($tribunal_viejo_nombre !== $tribunal_nuevo_nombre);
    
    if ($id_cambio || $nombre_cambio) {
        $son_diferentes = true;
        $cambios[] = "[CAMBIO] {$nombre_campo}: 'Trib. {$valor_viejo} - {$tribunal_viejo_nombre}' -> 'Trib. {$valor_nuevo} - {$tribunal_nuevo_nombre}'";
    }
}
```

### 2. Actualización en `diagnostico_tribunales.php`

Se expandió la sección de "Solución Recomendada" con:
- Explicación detallada del problema crítico
- Tres opciones de solución (A, B, C)
- Scripts SQL específicos para cada opción
- Advertencias de seguridad y recomendaciones

## Limitación Importante

⚠️ **LIMITACIÓN CRÍTICA:** Aunque el código ahora detecta y registra cambios entre tribunales con el mismo ID, **la base de datos seguirá guardando el mismo número**. 

**Ejemplo:**
- Usuario cambia de "Tribunal 64 - Nombre A" a "Tribunal 64 - Nombre B"
- El sistema detecta el cambio y lo registra en auditoría
- PERO la tabla maestro sigue guardando "64" en ambos casos
- Al recargar el expediente, puede mostrar cualquiera de los dos nombres asociados al ID 64

## Solución Definitiva (Requiere Limpieza de Base de Datos)

### Opción A: Eliminar Tribunal Duplicado (Recomendado si no se usa)

```sql
-- Paso 1: Verificar qué expedientes usan cada tribunal
SELECT m.n_expediente, m.id_tribunal, t.tribunal 
FROM maestro m 
LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal 
WHERE m.id_tribunal = 64;  -- Cambiar por el ID duplicado

-- Paso 2: Si uno NO se usa, eliminarlo
DELETE FROM tribunales 
WHERE id_tribunal = 64 AND tribunal = 'Nombre del tribunal a eliminar'
LIMIT 1;
```

### Opción B: Renumerar Tribunal Duplicado (Si ambos se usan)

```sql
-- Paso 1: Encontrar el próximo ID disponible
SELECT MAX(id_tribunal) + 1 as nuevo_id FROM tribunales;

-- Paso 2: Actualizar expedientes que usan el tribunal a renumerar
-- CUIDADO: Solo si quieres cambiar TODOS los expedientes de ese tribunal
UPDATE maestro 
SET id_tribunal = 999  -- Usar el nuevo ID
WHERE id_tribunal = 64;

-- Paso 3: Actualizar el tribunal en la tabla tribunales
UPDATE tribunales 
SET id_tribunal = 999 
WHERE id_tribunal = 64 AND tribunal = 'Nombre del tribunal a renumerar'
LIMIT 1;
```

### Opción C: Solución Manual Selectiva

Si solo algunos expedientes deben usar el tribunal duplicado:

1. Ejecutar `diagnostico_tribunales.php` para ver los duplicados
2. Decidir qué expedientes deben usar cada tribunal
3. Renumerar UNO de los tribunales duplicados a un nuevo ID
4. Actualizar manualmente los expedientes específicos que deben usar el tribunal renumerado

## Prevención de Duplicados Futuros

Después de limpiar los duplicados, ejecutar:

```sql
ALTER TABLE tribunales 
ADD UNIQUE KEY unique_id_tribunal (id_tribunal);
```

Esto impedirá que se creen nuevos tribunales con IDs duplicados.

## Recomendaciones

1. **Hacer respaldo completo** usando el módulo de Respaldo Total antes de cualquier cambio
2. **Ejecutar `diagnostico_tribunales.php`** para identificar todos los IDs duplicados
3. **Revisar cada caso** para decidir la mejor estrategia (eliminar vs renumerar)
4. **Aplicar la restricción UNIQUE** después de limpiar para prevenir futuros duplicados
5. **Considerar migrar a IDs autoincrementales** en lugar de IDs manuales

## Archivos Modificados

- `editar_registro.php` - Mejora en detección de cambios de tribunal
- `diagnostico_tribunales.php` - Soluciones detalladas agregadas
- `SOLUCION_TRIBUNALES_DUPLICADOS.md` - Este documento

## Próximos Pasos

1. Usuario debe ejecutar `diagnostico_tribunales.php` para ver los duplicados exactos
2. Decidir estrategia de limpieza (Opción A, B o C)
3. Hacer respaldo completo
4. Ejecutar los comandos SQL correspondientes
5. Verificar que el sistema funcione correctamente
6. Aplicar restricción UNIQUE para prevenir futuros duplicados
