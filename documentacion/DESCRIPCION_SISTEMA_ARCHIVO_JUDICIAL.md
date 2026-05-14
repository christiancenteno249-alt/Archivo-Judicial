# Proyecto: Sistema de Gestión de Archivo Judicial (DAR Aragua)

Este documento proporciona una visión detallada del sistema, su propósito, arquitectura y los beneficios que aporta a la Dirección Administrativa Regional (DAR) del estado Aragua.

---

## 1. ¿Qué es el Sistema de Archivo Judicial?
Es una plataforma web integral diseñada para la **centralización, organización y control de expedientes judiciales**. El sistema actúa como un puente digital entre la documentación física y la gestión administrativa, permitiendo localizar en segundos cualquier registro que antes requería horas de búsqueda manual.

## 2. Funcionalidades Principales

### A. Registro y Control de Expedientes
- Permite ingresar nuevos expedientes con datos precisos: número, fecha de entrada, tribunal asignado, partes involucradas (demandante/demandado) y motivo del delito.
- **Validación Inteligente:** El sistema evita duplicados y asegura que los números de cédula/RIF tengan el formato correcto antes de guardar.
- **Estandarización:** Todo el texto se normaliza automáticamente a mayúsculas para mantener una base de datos limpia y profesional.

### B. Buscador Avanzado
- Localización instantánea mediante múltiples filtros: por número de expediente, por nombre de las partes, por rango de fechas o por número de legajo.
- Interfaz intuitiva que muestra resultados claros con acceso directo a la edición o historial.

### C. Gestión de Ubicaciones Físicas
- Es el corazón del orden: permite asignar a cada expediente una ubicación exacta (Sede, Estante, Fila, Cara y Caja).
- Facilita la centralización de expedientes en sedes específicas (ej. Palo Negro).

### D. Seguridad y Auditoría
- **Log de Movimientos:** Cada vez que se crea o edita un registro, el sistema guarda quién lo hizo, qué cambió y a qué hora. Esto garantiza la transparencia total.
- **Roles de Usuario:** Diferencia entre Administradores (acceso total, respaldos, auditoría) y Operadores (registro y búsqueda).

### E. Soporte de Impresión Profesional
- Generación de fichas de registro en formato carta, diseñadas para ser archivadas físicamente junto al expediente, con logos oficiales y colores corporativos.

---

## 3. Beneficios del Sistema

1.  **Eficiencia Operativa:** Reduce drásticamente el tiempo de respuesta ante solicitudes de expedientes. Lo que antes era buscar en libros o estantes al azar, ahora es un clic.
2.  **Integridad de la Información:** Al eliminar el error humano en formatos (mayúsculas/minúsculas, cédulas cortas), la data es 100% confiable para reportes estadísticos.
3.  **Preservación Histórica:** El sistema mantiene un rastro eterno de la vida del expediente, permitiendo ver su evolución a lo largo de los años.
4.  **Independencia y Seguridad:** Con herramientas de respaldo integradas (SQL y Excel), la institución siempre tiene control total de su información.
5.  **Modernización Institucional:** Proyecta una imagen de vanguardia tecnológica alineada con las directrices del Tribunal Supremo de Justicia (TSJ) y la Dirección Ejecutiva de la Magistratura (DEM).

---

## 4. Detalles Técnicos (Arquitectura)

- **Patrón de Diseño MVC:** El programa está construido bajo el modelo **Modelo-Vista-Controlador**, lo que significa que el código está organizado de forma que sea fácil de mantener y ampliar en el futuro.
- **Seguridad Backend:** Utiliza tecnologías modernas (PHP 8+, PDO) que protegen la base de datos contra ataques comunes.
- **Interfaz Adaptable:** Funciona perfectamente en computadoras de escritorio y tablets gracias al uso de Bootstrap 5.
- **Frontend Dinámico:** Implementa librerías como *Select2* para búsquedas rápidas en listas largas y *SweetAlert2* para notificaciones elegantes.

---

## 5. Conclusión
El **Sistema de Archivo Judicial** no es solo una base de datos; es una herramienta estratégica que transforma el caos del papel en un flujo de trabajo digital ordenado, seguro y eficiente. Es la base tecnológica para un sistema judicial más transparente y rápido en el estado Aragua.
