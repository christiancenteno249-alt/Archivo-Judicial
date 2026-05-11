# ✅ BORRADO LÓGICO IMPLEMENTADO

## Fecha: 13/04/2026
## Sistema: Archivo Judicial

---

## 📋 RESUMEN DE CAMBIOS

Se ha implementado exitosamente el **Borrado Lógico** para la tabla `usuarios_sistema` para solucionar el error de integridad referencial que ocurría al intentar eliminar usuarios que tenían registros asociados en `historial_movimientos` y `auditoria_log`.

---

## 🗄️ 1. CAMBIOS EN BASE DE DATOS

### Script SQL Ejecutado: `agregar_status_usuarios.sql`

```sql
ALTER TABLE usuarios_sistema 
ADD COLUMN status INT DEFAULT 1 NOT NULL 
COMMENT '1=Activo, 0=Inactivo/Eliminado';

UPDATE usuarios_sistema SET status = 1;
```

**Columna agregada:**
- **Nombre:** `status`
- **Tipo:** `INT`
- **Default:** `1` (activo)
- **Valores:**
  - `1` = Usuario Activo
  - `0` = Usuario Inactivo/Eliminado

---

## 📝 2. ARCHIVOS MODIFICADOS

### A. `gestionar_usuarios.php`

#### Cambio 1: Eliminación Lógica (línea ~120)
**ANTES:**
```php
$sql = "DELETE FROM usuarios_sistema WHERE id_usuario = :id";
```

**DESPUÉS:**
```php
$sql = "UPDATE usuarios_sistema SET status = 0 WHERE id_usuario = :id";
```

#### Cambio 2: Listar Solo Usuarios Activos (línea ~140)
**ANTES:**
```php
$stmt = $pdo->query("SELECT * FROM usuarios_sistema ORDER BY fecha_registro DESC");
```

**DESPUÉS:**
```php
$stmt = $pdo->query("SELECT * FROM usuarios_sistema WHERE status = 1 ORDER BY fecha_registro DESC");
```

#### Cambio 3: Editar Solo Usuarios Activos (línea ~150)
**ANTES:**
```php
$stmt = $pdo->prepare("SELECT * FROM usuarios_sistema WHERE id_usuario = :id");
```

**DESPUÉS:**
```php
$stmt = $pdo->prepare("SELECT * FROM usuarios_sistema WHERE id_usuario = :id AND status = 1");
```

---

### B. `login.php`

#### Cambio: Login Solo para Usuarios Activos (línea ~25)
**ANTES:**
```php
$sql = "SELECT * FROM usuarios_sistema WHERE usuario_nick = :usuario LIMIT 1";
```

**DESPUÉS:**
```php
$sql = "SELECT * FROM usuarios_sistema WHERE usuario_nick = :usuario AND status = 1 LIMIT 1";
```

**Mensaje de error actualizado:**
```php
$mensaje = 'Usuario o contraseña incorrectos, o usuario inactivo.';
```

---

## ✅ 3. CONFIRMACIÓN: INTEGRIDAD REFERENCIAL PRESERVADA

### ¿Los registros en historial_movimientos seguirán mostrando el nombre del usuario aunque esté desactivado?

**SÍ, CONFIRMADO.** ✅

**Razón:**

#### A. `ver_historial.php` (línea 38-40)
```php
LEFT JOIN usuarios_sistema u ON h.id_usuario = u.id_usuario
```

El uso de `LEFT JOIN` garantiza que:
- Si el usuario existe (activo o inactivo), se muestra su nombre
- Si el usuario fue desactivado (`status = 0`), **SIGUE APARECIENDO** su nombre
- Si el usuario no existe (NULL), se muestra "No registrado"

#### B. `auditoria.php` (línea 25)
```php
LEFT JOIN usuarios_sistema u ON a.id_usuario = u.id_usuario
```

El uso de `LEFT JOIN` garantiza que:
- Todos los registros de auditoría se muestran
- Los nombres de usuarios desactivados **SIGUEN VISIBLES**
- La integridad histórica se mantiene

---

## 🔒 4. VENTAJAS DEL BORRADO LÓGICO

### ✅ Ventajas Implementadas:

1. **Integridad Referencial Preservada**
   - No se rompen las relaciones con `historial_movimientos`
   - No se rompen las relaciones con `auditoria_log`
   - No hay errores de Foreign Key

2. **Trazabilidad Completa**
   - El historial de movimientos sigue mostrando quién registró cada expediente
   - Los logs de auditoría mantienen el nombre del usuario
   - Se puede auditar acciones de usuarios desactivados

3. **Recuperación Posible**
   - Si se desactiva un usuario por error, se puede reactivar
   - Solo cambiar `status = 1` para reactivar
   - No se pierde información

4. **Seguridad Mejorada**
   - Usuarios desactivados NO pueden hacer login
   - Usuarios desactivados NO aparecen en la lista de gestión
   - Usuarios desactivados NO se pueden editar

---

## 🧪 5. PRUEBAS REALIZADAS

### Escenario 1: Desactivar Usuario con Registros
- ✅ Usuario se desactiva correctamente (`status = 0`)
- ✅ No aparece en lista de usuarios activos
- ✅ No puede hacer login
- ✅ Sus registros en historial siguen mostrando su nombre

### Escenario 2: Ver Historial de Expediente
- ✅ Movimientos registrados por usuario desactivado muestran su nombre
- ✅ No hay campos vacíos ni "NULL"
- ✅ Información completa y legible

### Escenario 3: Ver Auditoría
- ✅ Acciones de usuarios desactivados siguen visibles
- ✅ Nombres de usuarios desactivados se muestran correctamente
- ✅ Trazabilidad completa mantenida

---

## 📊 6. CONSULTAS SQL ÚTILES

### Ver todos los usuarios (activos e inactivos)
```sql
SELECT id_usuario, nombre_full, usuario_nick, rol, status, fecha_registro 
FROM usuarios_sistema 
ORDER BY status DESC, fecha_registro DESC;
```

### Ver solo usuarios activos
```sql
SELECT * FROM usuarios_sistema WHERE status = 1;
```

### Ver solo usuarios desactivados
```sql
SELECT * FROM usuarios_sistema WHERE status = 0;
```

### Reactivar un usuario
```sql
UPDATE usuarios_sistema SET status = 1 WHERE id_usuario = X;
```

### Contar usuarios por estado
```sql
SELECT 
    status,
    COUNT(*) as total,
    CASE 
        WHEN status = 1 THEN 'Activos'
        WHEN status = 0 THEN 'Inactivos'
    END as estado
FROM usuarios_sistema 
GROUP BY status;
```

---

## 🎯 7. CONCLUSIÓN

El borrado lógico ha sido implementado exitosamente y resuelve completamente el problema de integridad referencial. Los usuarios desactivados:

- ✅ NO pueden hacer login
- ✅ NO aparecen en la lista de gestión
- ✅ NO se pueden editar
- ✅ PERO sus nombres siguen apareciendo en historial y auditoría

**La trazabilidad histórica está 100% preservada.**

---

## 📞 SOPORTE

Si necesitas reactivar un usuario desactivado, ejecuta:
```sql
UPDATE usuarios_sistema SET status = 1 WHERE id_usuario = [ID_DEL_USUARIO];
```

---

**Implementado por:** Kiro AI Assistant  
**Fecha:** 13 de Abril de 2026  
**Estado:** ✅ COMPLETADO Y PROBADO
