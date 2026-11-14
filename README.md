# Sistemas MLARI — Carrera de Ingeniería UTP

**README principal del proyecto MLARI.** Aquí se describen sus funciones, tecnologías, requisitos e instalación. Los archivos de estado técnico son registros históricos y pueden reflejar pruebas anteriores.

**Diseño y desarrollo de MLARI:** Eomar Frank, alumno de la Universidad Tecnológica del Perú (UTP). Proyecto académico personal orientado al monitoreo y diagnóstico de una red de laboratorio.

## ¿Qué es MLARI?

MLARI es un sistema web de control de red. Reúne en un solo panel el inventario de dispositivos, su disponibilidad, las interfaces, los gráficos históricos y el estado de la recolección. Su objetivo es que el usuario pueda identificar rápidamente qué equipo responde, cuándo fue consultado por última vez y qué datos respaldan cada estado.

El laboratorio de referencia contiene routers, switches y equipos MikroTik virtualizados en EVE-NG. El mapa representa **12 nodos y 16 enlaces declarados**. Un enlace del dibujo no se considera operativo hasta contar con datos recientes de sus puertos; MLARI lo indica como **no verificado** cuando falta esa evidencia.

## Funciones del sistema

| Sección | Qué muestra o permite hacer |
|---|---|
| Resumen | Conteo de equipos operativos, caídos y sin datos recientes; fecha del último sondeo. |
| Topología | Mapa SVG interactivo con filtros, zoom, desplazamiento y posiciones guardadas en el navegador. |
| Dispositivos | Inventario con búsqueda por nombre o IP, estado, último sondeo y disponibilidad histórica. |
| Interfaces | Datos SNMP de puertos y aviso cuando el caché de interfaces está desactualizado. |
| Rendimiento | Gráficos históricos asociados a cada dispositivo. |
| Alertas | Incidencias y fechas de caída o recuperación ya registradas; esta pantalla no envía notificaciones por sí sola. |
| Diagnóstico | Estado de la recolección y datos observados del equipo de referencia R8. |
| Reportes | Resumen de estados y descarga del inventario en CSV. |
| Configuración | Información del origen de datos, actualización de la vista y restauración del mapa. |
| IA MLARI | Asistente opcional que analiza el contexto disponible y propone pasos de diagnóstico, sin cambiar la red. |

Los estados distinguen **alcanzable, caído, recuperando, deshabilitado y datos obsoletos**. El panel actualiza su consulta al servidor cada **60 segundos**. La actualización del navegador no equivale a un nuevo sondeo de red: el recolector debe estar funcionando para generar muestras nuevas.

## Tecnologías utilizadas

### Frontend

- **HTML5 y CSS3:** estructura de las pantallas, diseño adaptable, tablas, indicadores y paneles.
- **JavaScript sin framework de interfaz:** navegación entre secciones, filtros, búsqueda, detalles de dispositivos y actualización de contenido.
- **SVG:** dibujo del mapa de topología y sus elementos interactivos.
- **Fetch API y JSON:** comunicación asíncrona con los servicios del backend.
- **`localStorage`:** guarda las posiciones personalizadas del mapa en el navegador del usuario.

### Backend y datos

- **PHP 8.2 o superior:** lógica del sistema, páginas, API JSON, exportación CSV y acceso a datos.
- **MariaDB 10.5 o superior:** inventario, estados, historial de sondeos, caché de interfaces y referencias a gráficos.
- **Apache en XAMPP:** servidor web para la instalación local en Windows.
- **ICMP y SNMP:** comprobación de disponibilidad y lectura de información de equipos compatibles.
- **RRDtool 1.7 o superior:** almacenamiento de series de tiempo y generación de gráficos históricos.
- **Programador de tareas de Windows:** ejecución periódica del recolector.
- **Composer 2:** instalación de bibliotecas PHP definidas por el proyecto.
- **API de Gemini, opcional:** respuestas del asistente IA mediante HTTPS. Requiere una clave propia en `GEMINI_API_KEY`, accesible para Apache. La clave nunca debe guardarse en GitHub.

## Requisitos antes de instalar

Windows de 64 bits, conexión a Internet para descargar dependencias y permisos para instalar programas. El instalador prepara XAMPP, RRDtool, herramientas SNMP y bibliotecas PHP que falten. Para ver datos reales también se necesita acceso de red a los equipos, direcciones y rutas correctas, permisos SNMP y un recolector programado.

**EVE-NG es opcional para abrir MLARI.** Solo se necesita si se desea reproducir el laboratorio virtual y recibir telemetría de sus nodos. Si la VM o un equipo están apagados, no habrá muestras nuevas de ellos.

## Instalar desde CMD

```cmd
git clone https://github.com/Mendoza-frank/sistemas-mlari-ingenieria-utp.git
cd sistemas-mlari-ingenieria-utp
instalar-mlari.bat
```

El instalador primero descarga y verifica los componentes faltantes; después inicia su instalación. En un equipo nuevo utiliza una versión de XAMPP con PHP y MariaDB compatibles. **No reemplaza una instalación existente ni sus bases de datos.** Si detecta versiones antiguas, se detiene para que el usuario haga un respaldo y planifique la migración.

Después de instalar las herramientas, crea una base y una cuenta propias, copia `include/config.php.dist` a `include/config.php` y coloca allí los datos de conexión. Termina el asistente web local, activa MLARI, configura el sondeo periódico y agrega los equipos que quieras monitorear. Las credenciales, la configuración local y los datos históricos no se incluyen en el repositorio público.

Para revisar requisitos sin descargar ni modificar archivos:

```cmd
powershell -NoProfile -ExecutionPolicy Bypass -File .\instalar-mlari.ps1 -Comprobar
```

## Uso diario

Inicia Apache y MariaDB en XAMPP, abre la dirección local de MLARI y comprueba que la tarea de recolección esté activa. Enciende aparte los equipos que desees monitorear. Un estado antiguo debe leerse junto con la hora del último sondeo; la pantalla no supone que un enlace funciona solo porque sus extremos aparecen encendidos.

## Archivos del proyecto

- `README.md`: guía principal que estás leyendo.
- `instalar-mlari.bat` y `instalar-mlari.ps1`: preparación del entorno Windows.
- `plugins/mlari/`: interfaz, API, topología y asistente IA de MLARI.
- `include/config.php.dist`: plantilla de configuración; `include/config.php` contiene datos privados y no se publica.
- `log/`, `rra/` y `cache/`: datos generados en cada instalación.

## Licencias

MLARI conserva los avisos de autoría y las licencias de los componentes de software incluidos. Consulta [LICENSE](LICENSE) y los archivos de licencia correspondientes antes de redistribuir el proyecto.
