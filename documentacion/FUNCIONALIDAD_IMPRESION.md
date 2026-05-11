# Funcionalidad de Impresión de Expedientes

## Resumen

Se ha implementado una funcionalidad completa para imprimir expedientes desde cualquier parte del sistema. Los usuarios pueden generar un comprobante profesional con toda la información del expediente.

---

## Archivos Creados

### 1. `imprimir_expediente.php`
Archivo principal que genera el comprobante de impresión.

**Características:**
- ✅ Diseño profesional con encabezado institucional
- ✅ Información completa del expediente
- ✅ Formato optimizado para impresión
- ✅ Botones flotantes (Imprimir y Volver)
- ✅ Se abre en nueva pestaña
- ✅ Estilos específicos para impresión (@media print)
- ✅ Pie de página con información del usuario y fecha/hora

**Secciones del Comprobante:**
1. **Encabezado Institucional:**
   - República Bolivariana de Venezuela
   - Dirección Ejecutiva de la Magistratura
   - Dirección Administrativa Regional del Estado Aragua
   - Archivo Judicial de la Circunscripción Judicial del Estado Aragua

2. **Datos del Expediente:**
   - N° Expediente
   - Fecha de Ingreso
   - Tribunal Asignado
   - N° Legajo

3. **Partes Involucradas:**
   - Demandante (nombre y CI/RIF)
   - Demandado (nombre y CI/RIF)

4. **Detalles del Caso:**
   - Motivo / Delito
   - Observaciones (si existen)

5. **Pie de Página:**
   - Nombre del sistema
   - Usuario que imprimió
   - Fecha y hora de impresión

---

## Archivos Modificados

### 1. `buscador.php`
**Cambio:** Agregado botón de impresión en la columna de acciones.

**Antes:**
```html
<div class="btn-group">
    <a href="ver_historial.php?..." class="btn btn-sm btn-primary">Ver más</a>
    <a href="editar_registro.php?..." class="btn btn-sm btn-warning">Editar</a>
</div>
```

**Ahora:**
```html
<div class="btn-group">
    <a href="ver_historial.php?..." class="btn btn-sm btn-primary">Ver más</a>
    <a href="editar_registro.php?..." class="btn btn-sm btn-warning">Editar</a>
    <a href="imprimir_expediente.php?..." class="btn btn-sm btn-success">Imprimir</a>
</div>
```

**Resultado:**
- Botón verde con icono de impresora
- Se abre en nueva pestaña (target="_blank")
- Ubicado al lado del botón de editar

---

### 2. `ver_historial.php`
**Cambio:** Agregado botón de impresión en la parte superior derecha.

**Antes:**
```html
<div class="mb-4">
    <a href="buscador.php" class="btn btn-secondary">Volver</a>
</div>
```

**Ahora:**
```html
<div class="mb-4 d-flex justify-content-between">
    <div>
        <a href="buscador.php" class="btn btn-secondary">Volver</a>
    </div>
    <div>
        <a href="imprimir_expediente.php?..." class="btn btn-success">Imprimir Expediente</a>
    </div>
</div>
```

**Resultado:**
- Botón "Volver" a la izquierda
- Botón "Imprimir Expediente" a la derecha
- Diseño balanceado con flexbox

---

## Ubicaciones de los Botones de Impresión

### 1. En el Buscador (`buscador.php`)
**Ubicación:** Columna "Acciones" de cada fila de resultados  
**Apariencia:** Botón verde pequeño con icono de impresora  
**Comportamiento:** Abre la vista de impresión en nueva pestaña  

### 2. En el Historial (`ver_historial.php`)
**Ubicación:** Parte superior derecha, al lado del botón "Volver"  
**Apariencia:** Botón verde con texto "Imprimir Expediente"  
**Comportamiento:** Abre la vista de impresión en nueva pestaña  

---

## Flujo de Uso

### Desde el Buscador:
1. Usuario busca expedientes
2. Resultados se muestran en tabla
3. Usuario hace clic en botón verde de impresora
4. Se abre nueva pestaña con vista de impresión
5. Usuario puede imprimir o volver

### Desde el Historial:
1. Usuario ve detalles de un expediente
2. Usuario hace clic en "Imprimir Expediente" (arriba a la derecha)
3. Se abre nueva pestaña con vista de impresión
4. Usuario puede imprimir o volver

### En la Vista de Impresión:
1. Se muestra comprobante profesional
2. Botón flotante "Imprimir" (abajo derecha) → Abre diálogo de impresión
3. Botón flotante "Volver" (abajo izquierda) → Regresa al buscador
4. Los botones NO se imprimen (clase .no-print)

---

## Características Técnicas

### Estilos de Impresión (@media print)
```css
@media print {
    .no-print { display: none !important; }
    body { background: white !important; }
    .print-container { box-shadow: none !important; border: 1px solid #000 !important; }
}
```

**Resultado:**
- Botones flotantes no se imprimen
- Fondo blanco para ahorrar tinta
- Borde negro para delimitar el documento
- Sin sombras ni efectos visuales

### Seguridad
- ✅ Requiere autenticación (`require_once "auth_check.php"`)
- ✅ Valida que el ID sea numérico
- ✅ Verifica que el expediente exista
- ✅ Usa prepared statements (PDO)
- ✅ Escapa HTML con `htmlspecialchars()`

### Información de Auditoría
El pie de página incluye:
- Nombre del usuario que imprimió
- Fecha de impresión (dd/mm/yyyy)
- Hora de impresión (HH:mm:ss)

---

## Ventajas de Esta Implementación

1. ✅ **Accesible desde múltiples lugares:** Buscador y Historial
2. ✅ **Diseño profesional:** Encabezado institucional oficial
3. ✅ **Optimizado para impresión:** Estilos específicos @media print
4. ✅ **No interfiere con el flujo:** Se abre en nueva pestaña
5. ✅ **Información completa:** Todos los datos del expediente
6. ✅ **Trazabilidad:** Registra quién y cuándo imprimió
7. ✅ **Fácil de usar:** Un solo clic para imprimir
8. ✅ **Ahorra tinta:** Fondo blanco, sin elementos innecesarios

---

## Personalización Futura

Si necesitas modificar el comprobante, edita `imprimir_expediente.php`:

### Cambiar el Encabezado:
```php
<h1>REPÚBLICA BOLIVARIANA DE VENEZUELA</h1>
<h2>Tu Institución Aquí</h2>
```

### Agregar Más Campos:
```php
<div class="row">
    <div class="col-12">
        <span class="print-label">Nuevo Campo</span>
        <div class="print-value"><?= htmlspecialchars($expediente['nuevo_campo']) ?></div>
    </div>
</div>
```

### Cambiar Colores:
```css
.header-print h1 { color: #1a237e; } /* Azul institucional */
.print-label { color: #1a237e; }
.section-title { background-color: #1a237e; }
```

---

## Pruebas Recomendadas

1. ✅ Imprimir desde el buscador
2. ✅ Imprimir desde el historial
3. ✅ Verificar que se abra en nueva pestaña
4. ✅ Verificar que los botones no se impriman
5. ✅ Verificar que la información sea correcta
6. ✅ Probar con expedientes con y sin observaciones
7. ✅ Verificar el pie de página con usuario y fecha

---

## Notas Importantes

⚠️ **IMPORTANTE:** El botón abre una nueva pestaña (`target="_blank"`) para no interrumpir el flujo de trabajo del usuario.

✅ **COMPATIBLE:** Funciona en todos los navegadores modernos (Chrome, Firefox, Edge, Safari).

✅ **RESPONSIVE:** El diseño se adapta al tamaño del papel (A4, Carta, etc.).

✅ **SEGURO:** Solo usuarios autenticados pueden imprimir expedientes.

---

## Resumen de Cambios

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `imprimir_expediente.php` | NUEVO | Vista de impresión profesional |
| `buscador.php` | MODIFICADO | Botón de impresión en tabla de resultados |
| `ver_historial.php` | MODIFICADO | Botón de impresión en parte superior |
| `FUNCIONALIDAD_IMPRESION.md` | NUEVO | Esta documentación |

---

## Soporte

Si necesitas ayuda o quieres personalizar el comprobante:
1. Revisa este documento
2. Edita `imprimir_expediente.php` según tus necesidades
3. Prueba los cambios imprimiendo un expediente de prueba
