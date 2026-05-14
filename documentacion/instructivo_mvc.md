# Instructivo Técnico — Migración MVC
## Sistema Archivo Judicial · Fases 5–8

---

## ¿Qué logramos?

Migramos el sistema de un conjunto de scripts PHP sueltos a una **arquitectura MVC (Modelo-Vista-Controlador)** limpia y mantenible, usando el **Patrón Estrangulador** para no interrumpir el funcionamiento en ningún momento.

> **Clave:** Los archivos legacy (`buscador.php`, `registrar.php`, `editar_registro.php`, etc.) **no se eliminaron**. Siguen en el disco como red de seguridad. Si algo falla, puedes revertir el `.htaccess` en 2 líneas.

---

## Estructura de archivos creados

```
Archivo-Judicial/
├── index.php               ← MODIFICADO: Ahora es el Front Controller
├── router.php              ← MODIFICADO: Delega todo a index.php
├── .htaccess               ← MODIFICADO: 3 reglas simples → MVC
│
├── Core/                   ← NUEVO: Núcleo del framework MVC
│   ├── App.php             ← Enrutador (URL → Controlador::método)
│   ├── Controller.php      ← Clase base de todos los controladores
│   └── Database.php        ← Conexión única PDO (Singleton)
│
├── Models/                 ← NUEVO: Toda la lógica de base de datos
│   ├── Expediente.php      ← Buscar, Guardar, Actualizar, Historial
│   ├── Usuario.php         ← CRUD de usuarios_sistema
│   └── Sede.php            ← CRUD de sedes_deposito
│
├── Controllers/            ← NUEVO: Coordinan Model ↔ View
│   ├── ExpedienteController.php  ← buscar, registrar, editar, historial, imprimir
│   ├── UsuarioController.php     ← index (lista + crear + editar + eliminar)
│   └── SedeController.php        ← index (lista + crear + editar + toggle)
│
└── Views/                  ← NUEVO: Solo HTML/PHP de presentación
    ├── expediente/
    │   ├── buscar.php
    │   ├── registrar.php
    │   ├── editar.php
    │   ├── historial.php
    │   └── imprimir.php
    ├── usuario/
    │   └── index.php
    └── sede/
        └── index.php
```

---

## Flujo de una petición (antes vs. ahora)

### Antes (Legacy)
```
Usuario → /consulta → .htaccess → buscador.php
                                   (SQL + HTML mezclados, 49 KB de código)
```

### Ahora (MVC)
```
Usuario → /consulta → .htaccess → index.php (Front Controller)
                                   → Core/App.php (enrutador)
                                   → ExpedienteController::buscar()
                                     → Models/Expediente::buscar() ← solo SQL
                                     → Views/expediente/buscar.php ← solo HTML
```

---

## Qué hace cada archivo nuevo

### `Core/Database.php` — Conexión Singleton
- Una **única instancia PDO** para toda la aplicación (no se crean múltiples conexiones).
- Configurada con `ERRMODE_EXCEPTION` para que los errores SQL sean manejables.
- Si la conexión falla, retorna un JSON de error en lugar de mostrar credenciales.

### `Core/Controller.php` — Clase Base
Todos los controladores heredan de esta. Provee:
| Método | Qué hace |
|--------|----------|
| `render($vista, $datos)` | Carga el archivo de vista pasándole variables |
| `requireAuth()` | Si no hay sesión activa, redirige a `/login` |
| `requireAdmin()` | Si el rol no es `admin`, redirige al inicio |
| `redirect($url)` | Redirige y termina la ejecución |
| `auditoria($accion, $recurso, $detalles)` | Registra en `auditoria_log` |

### `Core/App.php` — Enrutador
- Tabla de rutas estáticas: `'consulta' => [ExpedienteController, buscar]`
- Tabla de rutas dinámicas con regex: `/editar/5` → `ExpedienteController::editar(5)`
- Si la URL no coincide con ninguna ruta MVC → responde 404 (el `index.php` puede capturarlo y hacer fallback)

### `index.php` — Front Controller
1. Registra el **Autoloader**: carga automáticamente cualquier clase de `Core/`, `Models/`, `Controllers/` sin necesidad de `require_once` manuales.
2. Instancia `App` y llama `run()`.
3. Si `App` lanza una excepción (ruta no existe), intenta el fallback al archivo legacy.

---

## Módulo Expedientes — Métodos del Model

| Método | Descripción |
|--------|-------------|
| `buscar(array $filtros, int $pagina)` | Búsqueda con 11 filtros + paginación. Retorna resultados y metadata. |
| `guardar(array $datos, int $idUsuario)` | Inserta o actualiza según si ya existe el expediente. Siempre registra en `historial_movimientos`. |
| `actualizar(int $id, array $datos, array $anterior)` | Actualiza por ID primario. Detecta y devuelve qué campos cambiaron. |
| `obtenerPorId(int $id)` | SELECT con JOIN a `tribunales`. |
| `obtenerHistorial(string $n_expediente)` | `historial_movimientos` con JOIN a `tribunales` y `usuarios_sistema`. |
| `obtenerTribunales()` | Lista de tribunales para los `<select>`. |
| `existeExpedienteOtroId(string, int)` | Validación para edición: detecta número duplicado en otro registro. |
| `obtenerNombreTribunal(int $id)` | Nombre del tribunal por ID (usado en la vista de editar). |

---

## Cómo se maneja la auditoría en MVC

En el sistema legacy, la auditoría dependía de la variable global `$pdo` del archivo `auditoria_functions.php`. En MVC, el método `Controller::auditoria()` **replica exactamente la misma lógica** pero usando `$this->db` (la conexión Singleton), eliminando la dependencia del global.

Acciones registradas automáticamente:
- `CREAR_EXPEDIENTE` / `ACTUALIZAR_EXPEDIENTE` / `EDITAR_EXPEDIENTE`
- `CREAR_USUARIO` / `EDITAR_USUARIO` / `DESACTIVAR_USUARIO`
- `CREAR_SEDE` / `EDITAR_SEDE` / `CAMBIAR_ESTADO_SEDE`

---

## Cómo agregar un módulo nuevo en el futuro

1. **Crear el modelo** en `Models/NuevoModelo.php` con los métodos de BD.
2. **Crear el controlador** en `Controllers/NuevoController.php` que extienda `Controller`.
3. **Crear las vistas** en `Views/nuevo/`.
4. **Registrar la ruta** en `Core/App.php`:
   ```php
   'mi-ruta' => ['NuevoController', 'index'],
   ```
¡Listo! El Autoloader carga las clases automáticamente.

---

## Estrategia de rollback (si algo sale mal)

Revertir en 30 segundos — editar `.htaccess` y cambiar la regla 3:

```apache
# Antes (MVC):
RewriteRule ^(.*)$ index.php [L,QSA]

# Revertir a legacy (cambiar a esto):
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^consulta$ buscador.php [L,QSA]
# ... (las reglas originales)
```

---

## Rutas activas en el sistema

| URL limpia | Controlador MVC | Archivo legacy (backup) |
|------------|-----------------|-------------------------|
| `/` o `/inicio` | HomeController::index | index.php legacy |
| `/login` | AuthController::login | login.php |
| `/salir` | AuthController::logout | logout.php |
| `/consulta` | ExpedienteController::buscar | buscador.php |
| `/registro` | ExpedienteController::registrar | registrar.php |
| `/editar/{id}` | ExpedienteController::editar | editar_registro.php |
| `/expediente/{id}` | ExpedienteController::historial | ver_historial.php |
| `/imprimir/{id}` | ExpedienteController::imprimir | imprimir_expediente.php |
| `/usuarios` | UsuarioController::index | gestionar_usuarios.php |
| `/sedes` | SedeController::index | gestionar_sedes.php |
| `/ubicaciones` | (legacy via fallback) | gestionar_ubicaciones.php |
| `/respaldo` | (legacy via fallback) | respaldar_bd.php |
| `/auditoria` | AuditoriaController::index | auditoria.php |

---

## Beneficios de la arquitectura MVC

| Aspecto | Antes (Legacy) | Ahora (MVC) |
|---------|---------------|-------------|
| **Consultas SQL** | Mezcladas con HTML | Solo en Models/ |
| **HTML** | Mezclado con PHP lógico | Solo en Views/ |
| **Autenticación** | `require 'auth_check.php'` en cada archivo | `$this->requireAuth()` heredado |
| **Auditoría** | `global $pdo; registrarAccion(...)` | `$this->auditoria(...)` integrado |
| **Conexión BD** | Múltiples `require 'conexion.php'` | Una sola instancia Singleton |
| **Agregar módulo** | Copiar y modificar otro archivo de 400+ líneas | 3 archivos pequeños + 1 línea en App.php |
| **Testing** | Imposible sin cargar todo el HTML | Models testeables de forma independiente |

---

*Generado automáticamente · Sistema Archivo Judicial · Mayo 2026*
