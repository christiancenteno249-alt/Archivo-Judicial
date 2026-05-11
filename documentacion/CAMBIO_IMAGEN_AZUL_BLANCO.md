# Cambio de Imagen Global: Blanco y Azul

## Paleta de Colores Oficial

### Colores Principales
- **Azul Corporativo**: `#004085` (azul profesional DEM)
- **Azul Claro**: `#0056b3`
- **Azul Hover**: `#003366`
- **Blanco**: `#FFFFFF`
- **Gris Pálido**: `#f8f9fa` (para alternar filas)

### Colores de Alerta
- **Rojo Alerta**: `#DC3545` (acciones críticas/peligrosas)
- **Gris Neutro**: `#6c757d` (cancelar/volver)

## Archivos Actualizados

### ✅ Completados
1. **index.php** - Menú principal
   - Tarjetas con degradados azules
   - Bordes azules
   - Iconos azules
   - Fondo blanco

2. **buscador.php** - Búsqueda de expedientes
   - Header azul
   - Botones azules
   - Modal de ubicación con degradado azul
   - Iconos de ubicación azules (antes verdes/teal)
   - Tarjetas de información azules

### 🔄 Pendientes
3. **gestionar_ubicaciones.php**
4. **gestionar_usuarios.php**
5. **gestionar_sedes.php**
6. **auditoria.php**
7. **registrar.php**
8. **editar_registro.php**
9. **ver_historial.php**
10. **respaldar_bd.php**

## Cambios Específicos Realizados

### Reemplazos de Color
- `#00695c` (verde/teal) → `#004085` (azul corporativo)
- `#2e7d32` (verde) → `#0056b3` (azul claro)
- `#f57c00` (naranja) → `#004085` (azul corporativo)
- `#1976d2` (azul claro anterior) → `#004085` (azul corporativo)
- `#6a1b9a` (morado) → `#004085` (azul corporativo)
- `#c62828` (rojo) → `#003366` (azul oscuro)

### Degradados Actualizados
- Admin Usuarios: `linear-gradient(135deg, #004085 0%, #0056b3 100%)`
- Admin Auditoría: `linear-gradient(135deg, #003366 0%, #004085 100%)`
- Admin Respaldo: `linear-gradient(135deg, #0056b3 0%, #007bff 100%)`
- Ubicaciones: `linear-gradient(135deg, #004085 0%, #0056b3 100%)`
- Modal Ubicación: `linear-gradient(135deg, #004085 0%, #0056b3 100%)`

### Fondos de Tarjetas en Modal
- Sede: `#f8f9fa` (gris pálido)
- Área: `#e3f2fd` (azul muy claro)
- Estante: `#e3f2fd` (azul muy claro)

## Configuración de SweetAlert2

### Botones de Confirmación (Acciones Peligrosas)
```javascript
confirmButtonColor: '#DC3545'  // Rojo alerta
```

### Botones de Cancelación
```javascript
cancelButtonColor: '#6c757d'  // Gris neutro
```

### Iconos
- Error: Rojo
- Advertencia: Rojo
- Éxito: Azul corporativo
- Info: Azul claro

## Próximos Pasos

1. Actualizar `gestionar_ubicaciones.php` con colores azules
2. Actualizar todos los SweetAlert2 con botones rojos para acciones críticas
3. Revisar y actualizar tablas con fondo blanco
4. Actualizar badges y etiquetas con colores azules
5. Verificar consistencia en todos los archivos

## Notas Importantes

- Todos los verdes/teal deben ser reemplazados por azul
- Los fondos deben ser blancos (#FFFFFF)
- Las alertas críticas (eliminar, borrar, etc.) deben usar rojo (#DC3545)
- Los botones de cancelar deben usar gris neutro (#6c757d)
- Mantener sombras sutiles con rgba(0,64,133,0.X) para efecto azul
