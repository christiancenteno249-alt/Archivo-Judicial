# URLs Limpias del Sistema

## Configuración Implementada

Se ha implementado un sistema de URLs limpias usando `.htaccess` para mejorar la experiencia del usuario y la seguridad del sistema.

## Mapeo de URLs

### URLs Públicas (Requieren Autenticación)

| URL Limpia | URL Original | Descripción |
|------------|--------------|-------------|
| `/inicio` | `index.php` | Menú principal |
| `/consulta` | `buscador.php` | Búsqueda de expedientes |
| `/registro` | `registrar.php` | Registro de nuevo expediente |
| `/expediente/123` | `ver_historial.php?id=123` | Ver historial de expediente |
| `/editar/123` | `editar_registro.php?id=123` | Editar expediente |
| `/imprimir/123` | `imprimir_expediente.php?id=123` | Imprimir expediente |
| `/ubicaciones` | `gestionar_ubicaciones.php` | Gestión de ubicaciones |
| `/salir` | `logout.php` | Cerrar sesión |

### URLs Administrativas (Solo Admin)

| URL Limpia | URL Original | Descripción |
|------------|--------------|-------------|
| `/usuarios` | `gestionar_usuarios.php` | Gestión de usuarios |
| `/auditoria` | `auditoria.php` | Auditoría del sistema |
| `/respaldo` | `respaldar_bd.php` | Respaldo de base de datos |
| `/sedes` | `gestionar_sedes.php` | Gestión de sedes |

## Ejemplos de Uso

### Antes (URLs con parámetros visibles)
```
http://localhost/archivo_judicial/buscador.php?expediente=00001&ejecutar=1
http://localhost/archivo_judicial/ver_historial.php?id=123&search=00001
http://localhost/archivo_judicial/editar_registro.php?id=456
```

### Después (URLs limpias)
```
http://localhost/archivo_judicial/consulta
http://localhost/archivo_judicial/expediente/123
http://localhost/archivo_judicial/editar/456
```

## Ventajas

1. **Más Profesional**: URLs cortas y descriptivas
2. **Mejor UX**: Fáciles de recordar y compartir
3. **Seguridad**: Oculta la estructura interna del sistema
4. **SEO Friendly**: Aunque no aplica para sistemas internos, es buena práctica
5. **Mantenibilidad**: Cambios internos sin afectar URLs públicas

## Parámetros de Búsqueda

Los parámetros de búsqueda (query strings) se mantienen funcionales:

```
/consulta?expediente=00001&ejecutar=1
/auditoria?fecha_desde=2024-01-01&fecha_hasta=2024-12-31
/ubicaciones?modo=lote
```

La flag `QSA` (Query String Append) en `.htaccess` preserva estos parámetros.

## Seguridad Adicional

El archivo `.htaccess` también incluye:

- ✅ Protección de archivos sensibles (.md, .sql, .log)
- ✅ Protección de archivos de configuración (conexion.php, auth_check.php)
- ✅ Prevención de listado de directorios
- ✅ Headers de seguridad (X-Frame-Options, X-Content-Type-Options, X-XSS-Protection)

## Compatibilidad

- ✅ Apache 2.4+ (XAMPP incluye Apache)
- ✅ Requiere `mod_rewrite` habilitado (viene habilitado por defecto en XAMPP)
- ✅ Compatible con Windows, Linux y macOS

## Notas Importantes

1. **RewriteBase**: Configurado para `/archivo_judicial/` - ajustar si el proyecto está en otra ruta
2. **Archivos Reales**: Los archivos PHP originales siguen funcionando si se accede directamente
3. **Retrocompatibilidad**: Los enlaces antiguos siguen funcionando
4. **Actualización Gradual**: Se pueden actualizar los enlaces internos progresivamente

## Próximos Pasos

Para completar la implementación, actualizar los enlaces en:
- [x] `.htaccess` creado
- [ ] `index.php` - Enlaces del menú principal
- [ ] `buscador.php` - Enlaces a ver historial y editar
- [ ] `ver_historial.php` - Botón de editar
- [ ] Otros archivos con enlaces internos

## Verificación

Para verificar que funciona:

1. Acceder a: `http://localhost/archivo_judicial/consulta`
2. Debe mostrar la página de búsqueda de expedientes
3. Acceder a: `http://localhost/archivo_judicial/inicio`
4. Debe mostrar el menú principal
