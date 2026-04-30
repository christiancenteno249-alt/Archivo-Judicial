# Eliminación del Campo "Estado del Caso" del Formulario de Registro

## Cambios Realizados

### Archivo: `registrar.php`

#### 1. Eliminación del Campo del Formulario HTML
- **Antes:** Fila 1 tenía 3 campos (N° Expediente, Fecha, Estado del Caso) en col-md-4
- **Ahora:** Fila 1 tiene 2 campos (N° Expediente, Fecha) en col-md-6
- **Resultado:** Diseño más limpio y espacioso

#### 2. Eliminación de la Variable PHP
```php
// ELIMINADO:
$status = trim($_POST['status'] ?? '');
```

#### 3. Actualización de la Validación
- El campo `status` ya NO se valida en el servidor
- La validación sigue requiriendo: Expediente, Fecha, Tribunal, Demandante, Demandado, Motivo/Delito, N° Legajo

#### 4. Lógica de INSERT (Nuevos Expedientes)
- **Cambio importante:** Ahora se asigna automáticamente `status = 'Activo'` al crear un expediente nuevo
```php
INSERT INTO maestro (..., status, ...) 
VALUES (..., 'Activo', ...)
```

#### 5. Lógica de UPDATE (Expedientes Existentes)
- El campo `status` ya NO se actualiza desde el formulario de registro
- El status solo se puede cambiar desde el formulario de edición (`editar_registro.php`)

#### 6. Comparación de Cambios
- Eliminado `'status' => 'Estado'` del array `$campos_nombres`
- Eliminado `'status' => $status` del array `$datos_nuevos`

## Comportamiento del Sistema

### Al Registrar un Expediente Nuevo
1. Usuario llena el formulario (sin campo de estado)
2. Sistema asigna automáticamente `status = 'Activo'`
3. Expediente se crea con estado "Activo" por defecto

### Al Actualizar un Expediente Existente (desde registro)
1. Usuario llena el formulario con número de expediente existente
2. Sistema actualiza los campos modificados
3. El campo `status` NO se modifica (mantiene su valor actual)

### Para Cambiar el Estado de un Expediente
- Usar el formulario de **Edición** (`editar_registro.php`)
- Allí sí existe el campo "Estado del Caso" con las opciones:
  - Activo
  - Archivado
  - Decidido

## Ventajas de Este Cambio

1. **Simplificación del Registro:** Menos campos = registro más rápido
2. **Consistencia:** Todos los expedientes nuevos inician como "Activo"
3. **Separación de Responsabilidades:**
   - Registro = Crear expedientes nuevos
   - Edición = Modificar expedientes existentes (incluyendo estado)
4. **Menos Errores:** No se puede asignar un estado incorrecto al registrar

## Archivos NO Modificados

Los siguientes archivos mantienen el campo `status`:
- `editar_registro.php` - Sí tiene campo de estado (correcto)
- `buscador.php` - Muestra el estado en resultados (correcto)
- `ver_historial.php` - Muestra el estado en el historial (correcto)

## Notas Importantes

⚠️ **IMPORTANTE:** El campo `status` sigue existiendo en la base de datos y en otros formularios. Solo se eliminó del formulario de REGISTRO inicial.

✅ **VALIDADO:** Todos los expedientes nuevos se crearán automáticamente con estado "Activo".

✅ **COMPATIBLE:** Los expedientes existentes mantienen su estado actual sin cambios.
