# Instrucciones para Imprimir sin URL en el Pie de Página

## Problema

Al imprimir desde el navegador, aparece la URL `localhost/archivo_judicial/imprimir_expediente.php?id=X` en el pie de página. Esto es un comportamiento predeterminado del navegador.

---

## Solución: Configurar el Navegador

### Google Chrome / Microsoft Edge

1. Hacer clic en el botón **"Imprimir"** o presionar `Ctrl + P`
2. En el diálogo de impresión, hacer clic en **"Más configuraciones"**
3. Buscar la opción **"Encabezados y pies de página"**
4. **Desmarcar** la casilla "Encabezados y pies de página"
5. Hacer clic en **"Imprimir"**

**Resultado:** La URL ya no aparecerá en la impresión.

---

### Mozilla Firefox

1. Hacer clic en el botón **"Imprimir"** o presionar `Ctrl + P`
2. En el diálogo de impresión, hacer clic en **"Configuración de página"** (o ir a Archivo → Configurar página)
3. En la pestaña **"Márgenes y encabezado/pie de página"**
4. Para cada opción (Encabezado izquierdo, centro, derecho y Pie de página izquierdo, centro, derecho):
   - Seleccionar **"--en blanco--"** en el menú desplegable
5. Hacer clic en **"Aceptar"**
6. Hacer clic en **"Imprimir"**

**Resultado:** La URL ya no aparecerá en la impresión.

---

### Safari (Mac)

1. Hacer clic en el botón **"Imprimir"** o presionar `Cmd + P`
2. En el diálogo de impresión, hacer clic en **"Mostrar detalles"**
3. Buscar la opción **"Encabezados y pies de página"**
4. **Desmarcar** la casilla
5. Hacer clic en **"Imprimir"**

**Resultado:** La URL ya no aparecerá en la impresión.

---

## Solución Alternativa: Guardar como PDF

Si no quieres configurar el navegador cada vez, puedes guardar como PDF:

### Pasos:

1. Hacer clic en el botón **"Imprimir"** o presionar `Ctrl + P`
2. En **"Destino"**, seleccionar **"Guardar como PDF"** o **"Microsoft Print to PDF"**
3. Desmarcar **"Encabezados y pies de página"** (si está disponible)
4. Hacer clic en **"Guardar"**
5. Elegir ubicación y nombre del archivo
6. Abrir el PDF guardado e imprimirlo desde allí

**Ventaja:** El PDF no tendrá la URL y puedes imprimirlo cuantas veces quieras sin configurar nada.

---

## Configuración Permanente (Recomendado)

### Chrome/Edge - Configuración Permanente:

1. Abrir Chrome/Edge
2. Ir a `chrome://settings/printing` (o `edge://settings/printing`)
3. En **"Encabezados y pies de página"**, desactivar la opción
4. Esta configuración se guardará para futuras impresiones

### Firefox - Configuración Permanente:

1. Ir a **Archivo → Configurar página**
2. Configurar todos los encabezados y pies de página como **"--en blanco--"**
3. Hacer clic en **"Aceptar"**
4. Esta configuración se guardará para futuras impresiones

---

## Resumen Rápido

| Navegador | Solución Rápida |
|-----------|-----------------|
| **Chrome/Edge** | `Ctrl + P` → Más configuraciones → Desmarcar "Encabezados y pies de página" |
| **Firefox** | `Ctrl + P` → Configurar página → Seleccionar "--en blanco--" en todos los campos |
| **Safari** | `Cmd + P` → Mostrar detalles → Desmarcar "Encabezados y pies de página" |

---

## Nota Técnica

La URL que aparece en el pie de página es generada por el navegador, no por el código PHP/HTML. Por eso no se puede eliminar directamente desde el código. Los estilos CSS `@page` ayudan a reducir márgenes, pero la configuración final depende del navegador.

---

## Recomendación para Usuarios del Sistema

**Opción 1 (Más Fácil):**
- Guardar como PDF primero
- Luego imprimir el PDF

**Opción 2 (Más Rápido):**
- Configurar el navegador una sola vez (configuración permanente)
- Todas las impresiones futuras saldrán sin URL

---

## Soporte

Si tienes dudas sobre cómo configurar tu navegador específico:
1. Revisa este documento
2. Busca en Google: "cómo imprimir sin URL [nombre de tu navegador]"
3. Consulta la ayuda oficial del navegador
