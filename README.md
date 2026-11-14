# MLARI — Centro de Control de Red

**README PRINCIPAL: instalación, uso y créditos de MLARI.** Los otros archivos `MLARI_*.md` y `CACTI_8_EQUIPOS_ESTADO.md` son notas técnicas históricas y pueden describir estados anteriores.

MLARI es una personalización de [Cacti](https://www.cacti.net/) para visualizar y analizar una red de laboratorio. El diseño y la adaptación de MLARI fueron realizados por **Eomar Frank, alumno de la Universidad Tecnológica del Perú (UTP)**. El motor Cacti y los complementos de terceros conservan sus autores y licencias; consulta [LICENSE](LICENSE) y las licencias de cada componente.

## Funciones

- Muestra dispositivos, disponibilidad, interfaces y gráficas históricas recolectadas por Cacti.
- Consulta equipos compatibles mediante ICMP y SNMP; RRDtool guarda y dibuja las series de tiempo.
- Incluye `plugins/mlari`, con centro de control de topología, consulta de datos y asistente de diagnóstico. El asistente explica los datos disponibles; no ejecuta cambios de red.
- Permite monitorear Cisco y MikroTik cuando están encendidos, configurados y alcanzables desde el servidor.

El asistente de diagnóstico con IA es opcional. Si deseas usarlo, configura una clave propia de Gemini en la variable de entorno `GEMINI_API_KEY` accesible para Apache. No coloques esa clave en el repositorio.

El laboratorio original usa ocho equipos en EVE-NG. **EVE-NG no es necesario para abrir MLARI**, pero sí debe estar encendido para obtener muestras nuevas de esos equipos. Las IP, credenciales y muestras históricas de esa instalación no se distribuyen con el código.

## Requisitos de Windows

| Componente | Uso | Versión mínima de esta rama de Cacti |
|---|---|---|
| XAMPP (Apache, PHP, MariaDB) | Web y base de datos | PHP 8.2+, MariaDB 10.5+ |
| RRDtool | Gráficas y archivos RRD | 1.7+ |
| Net-SNMP o PHP SNMP | Consultas SNMP | Net-SNMP 5.9+ |
| Composer | Bibliotecas de `composer.lock` | 2 |
| Programador de tareas | Sondeo periódico | Incluido en Windows |

El instalador nuevo descarga **XAMPP 8.3.36** para equipos nuevos, con PHP 8.3 y MariaDB 10.11. Si ya existe `C:\xampp`, **no lo reemplaza**: la base de datos podría perderse. La copia antigua de XAMPP 8.2.12 contenía MariaDB 10.4, por debajo del mínimo; para migrarla se necesita respaldo y un procedimiento aparte.

## Instalación

1. Descarga o clona el repositorio en una carpeta local de Windows de 64 bits.
2. Haz doble clic en **`instalar-mlari.bat`**. Primero descarga los componentes que faltan en `%LOCALAPPDATA%\MLARI\descargas` y verifica los archivos; después inicia sus instaladores y prepara el enlace `C:\xampp\htdocs\cacti`.
3. Si aparece el instalador de XAMPP, completa su asistente en `C:\xampp`. Windows puede solicitar permiso de administrador. El script espera a que termines el asistente.
4. Crea una base de datos y una cuenta **nuevas** para Cacti según la [guía oficial de Windows](https://docs.cacti.net/Installing-Under-Windows.md). Copia `include/config.php.dist` a `include/config.php` y coloca allí los datos de conexión de *tu* base; nunca publiques ese archivo.
5. Inicia Apache y MariaDB desde el panel XAMPP. Abre [http://localhost/cacti/](http://localhost/cacti/) y termina el asistente web de Cacti. Activa el complemento MLARI desde *Consola → Complementos* si hace falta.
6. Configura el sondeo periódico de Cacti y la tarea de Windows que ejecute `C:\xampp\php\php.exe <ruta-del-proyecto>\poller.php` con el intervalo elegido. Confirma resultado `0` y muestras en los RRD.
7. Añade tus equipos, rutas, credenciales SNMP y gráficos desde Cacti. En el laboratorio EVE-NG, enciende la VM y los nodos que quieras monitorear.

El instalador prepara el software; **la base, las contraseñas y la red se configuran para cada instalación**. No existe una contraseña universal ni se cargan datos privados del laboratorio.

### Comprobación sin instalar

`powershell -NoProfile -ExecutionPolicy Bypass -File .\instalar-mlari.ps1 -Comprobar`

Este modo muestra los componentes presentes sin descargar ni cambiar archivos.

## Uso diario

Inicia Apache y MariaDB en XAMPP y abre [http://localhost/cacti/](http://localhost/cacti/). Para datos nuevos, deben estar activos el recolector y los equipos de red. Un nodo apagado se mostrará caído y no generará muestras.

## Archivos importantes

- `README.md`: esta guía principal.
- `instalar-mlari.bat` y `instalar-mlari.ps1`: preparación de Windows.
- `plugins/mlari/`: personalización de MLARI.
- `include/config.php.dist`: plantilla pública; `include/config.php` es privado.
- `log/`, `rra/`, `cache/`: datos locales generados.

## Créditos

MLARI: **Eomar Frank (UTP)**. Motor de monitoreo: [Cacti](https://github.com/Cacti/cacti). Dependencias externas: [XAMPP](https://www.apachefriends.org/download.html), [RRDtool](https://github.com/oetiker/rrdtool-1.x), [Cygwin/Net-SNMP](https://cygwin.com/install.html) y [Composer](https://getcomposer.org/).
