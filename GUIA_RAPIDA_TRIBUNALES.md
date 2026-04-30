# Guía Rápida: Problema de Tribunales Duplicados

## ¿Qué pasó?

El sistema ahora detecta cuando intentas cambiar entre tribunales que tienen el mismo ID pero diferentes nombres. Sin embargo, **la base de datos solo guarda el número del tribunal, no el nombre**, por lo que aunque el sistema registre el cambio en auditoría, el expediente seguirá mostrando el mismo ID.

## ¿Qué se mejoró?

### ✅ Cambios Implementados

1. **Formulario de Edición (`editar_registro.php`)**
   - Ahora muestra el nombre del tribunal actual debajo del selector
   - Detecta cambios entre tribunales con el mismo ID pero diferentes nombres
   - Registra estos cambios en el log de auditoría con nombres completos

2. **Diagnóstico Mejorado (`diagnostico_tribunales.php`)**
   - Muestra soluciones detalladas con scripts SQL
   - Explica las 3 opciones disponibles (eliminar, renumerar, o solución temporal)
   - Incluye advertencias de seguridad

## ⚠️ Limitación Actual

**El problema NO está completamente resuelto** porque:

- La tabla `maestro` solo guarda el `id_tribunal` (número)
- Si cambias de "Tribunal 64 - Nombre A" a "Tribunal 64 - Nombre B"
- Ambos se guardan como "64" en la base de datos
- El sistema no puede distinguir cuál de los dos nombres mostrar

## 🔧 Solución Definitiva

Debes limpiar los IDs duplicados en la base de datos. Tienes 3 opciones:

### Opción 1: Eliminar el Tribunal Duplicado (Más Fácil)

**Cuándo usar:** Si uno de los tribunales duplicados NO se usa en ningún expediente.

```sql
-- Ver qué expedientes usan el tribunal 64
SELECT m.n_expediente, t.tribunal 
FROM maestro m 
LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal 
WHERE m.id_tribunal = 64;

-- Si uno de los nombres NO aparece, eliminarlo
DELETE FROM tribunales 
WHERE id_tribunal = 64 AND tribunal = 'Nombre a eliminar'
LIMIT 1;
```

### Opción 2: Renumerar el Tribunal Duplicado (Más Seguro)

**Cuándo usar:** Si ambos tribunales se usan en expedientes diferentes.

```sql
-- Encontrar el próximo ID disponible
SELECT MAX(id_tribunal) + 1 FROM tribunales;
-- Supongamos que devuelve 150

-- Renumerar UNO de los tribunales duplicados
UPDATE tribunales 
SET id_tribunal = 150 
WHERE id_tribunal = 64 AND tribunal = 'Nombre del tribunal a renumerar'
LIMIT 1;

-- Actualizar los expedientes que deben usar el tribunal renumerado
-- (Hacer esto MANUALMENTE para cada expediente específico)
UPDATE maestro 
SET id_tribunal = 150 
WHERE Id = 123;  -- ID del expediente específico
```

### Opción 3: No Hacer Nada (Temporal)

**Consecuencia:** El sistema seguirá mostrando cualquiera de los dos nombres asociados al ID duplicado de forma impredecible.

## 📋 Pasos Recomendados

1. **Hacer Respaldo**
   - Ir a `respaldar_bd.php`
   - Descargar respaldo en SQL y Excel
   - Guardar en lugar seguro

2. **Ejecutar Diagnóstico**
   - Abrir `diagnostico_tribunales.php`
   - Ver qué IDs están duplicados
   - Anotar cuáles tribunales se usan en expedientes

3. **Decidir Estrategia**
   - Si un tribunal NO se usa → Opción 1 (Eliminar)
   - Si ambos se usan → Opción 2 (Renumerar)
   - Si no quieres tocar la BD → Opción 3 (Convivir con el problema)

4. **Ejecutar SQL**
   - Abrir phpMyAdmin o tu gestor de base de datos
   - Copiar y pegar los comandos SQL
   - Ejecutar uno por uno
   - Verificar resultados

5. **Prevenir Futuros Duplicados**
   ```sql
   ALTER TABLE tribunales 
   ADD UNIQUE KEY unique_id_tribunal (id_tribunal);
   ```

## 🎯 Ejemplo Práctico

**Situación:** Tienes dos tribunales con ID 64:
- Tribunal 64 - "Juzgado Civil"
- Tribunal 64 - "Juzgado Mercantil"

**Paso 1:** Verificar uso
```sql
SELECT m.n_expediente, t.tribunal 
FROM maestro m 
LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal 
WHERE m.id_tribunal = 64;
```

**Resultado:**
- Expediente 001-24 usa "Juzgado Civil"
- Expediente 002-24 usa "Juzgado Mercantil"
- Expediente 003-24 usa "Juzgado Civil"

**Paso 2:** Renumerar "Juzgado Mercantil" a ID 150
```sql
UPDATE tribunales 
SET id_tribunal = 150 
WHERE id_tribunal = 64 AND tribunal = 'Juzgado Mercantil'
LIMIT 1;
```

**Paso 3:** Actualizar el expediente que lo usa
```sql
UPDATE maestro 
SET id_tribunal = 150 
WHERE n_expediente = '002-24';
```

**Paso 4:** Verificar
```sql
SELECT n_expediente, id_tribunal FROM maestro WHERE n_expediente IN ('001-24', '002-24', '003-24');
```

**Resultado esperado:**
- 001-24 → 64
- 002-24 → 150
- 003-24 → 64

## 📞 Soporte

Si tienes dudas o necesitas ayuda para ejecutar los comandos SQL, consulta:
- `SOLUCION_TRIBUNALES_DUPLICADOS.md` - Documentación completa
- `diagnostico_tribunales.php` - Herramienta de diagnóstico
- Administrador de base de datos de tu organización

## ⚡ Resumen Ultra-Rápido

1. Hacer respaldo → `respaldar_bd.php`
2. Ver duplicados → `diagnostico_tribunales.php`
3. Elegir: ¿Eliminar o Renumerar?
4. Ejecutar SQL en phpMyAdmin
5. Agregar restricción UNIQUE
6. ¡Listo! 🎉
