param([switch]$Comprobar)

$ErrorActionPreference = 'Stop'
$project = $PSScriptRoot
$php = 'C:\xampp\php\php.exe'
$mysqld = 'C:\xampp\mysql\bin\mysqld.exe'
$apache = 'C:\xampp\apache\bin\httpd.exe'
$snmp = 'C:\cygwin64\bin\snmpget.exe'
$rrd = 'C:\rrdtool\rrdtool-1.8.0-x64_vcpkg\rrdtool.exe'
$downloads = Join-Path $env:LOCALAPPDATA 'MLARI\descargas'

function Write-Step([string]$message) { Write-Host "[MLARI] $message" }
function Test-MinVersion([string]$value, [version]$minimum) {
    $match = [regex]::Match($value, '\d+\.\d+(?:\.\d+)?')
    if (-not $match.Success) { return $false }
    return [version]$match.Value -ge $minimum
}
function Get-Status {
    $phpVersion = if (Test-Path -LiteralPath $php) { (& $php -v | Select-Object -First 1) } else { 'Ausente' }
    $dbVersion = if (Test-Path -LiteralPath $mysqld) { (& $mysqld --version | Select-Object -First 1) } else { 'Ausente' }
    $rrdPath = if (Test-Path -LiteralPath $rrd) { $rrd } else {
        Get-ChildItem 'C:\rrdtool' -Filter 'rrdtool.exe' -Recurse -ErrorAction SilentlyContinue |
            Select-Object -First 1 -ExpandProperty FullName
    }
    [pscustomobject]@{
        PHP = $phpVersion
        PHPCompatible = Test-MinVersion $phpVersion ([version]'8.2')
        MariaDB = $dbVersion
        MariaDBCompatible = Test-MinVersion $dbVersion ([version]'10.5')
        Apache = Test-Path -LiteralPath $apache
        RRDtool = if ($rrdPath) { $rrdPath } else { 'Ausente' }
        NetSNMP = Test-Path -LiteralPath $snmp
        BibliotecasPHP = Test-Path -LiteralPath (Join-Path $project 'vendor\autoload.php')
        ConfiguracionPrivada = Test-Path -LiteralPath (Join-Path $project 'include\config.php')
    }
}
function Save-Download([string]$url, [string]$path, [string]$sha256) {
    if (Test-Path -LiteralPath $path) {
        if ((Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash -eq $sha256) {
            Write-Step "Descarga verificada: $(Split-Path $path -Leaf)"
            return
        }
        Remove-Item -LiteralPath $path -Force
    }
    Write-Step "Descargando $(Split-Path $path -Leaf)..."
    Invoke-WebRequest -Uri $url -OutFile $path -UseBasicParsing -MaximumRedirection 10
    if ((Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash -ne $sha256) {
        Remove-Item -LiteralPath $path -Force
        throw "La descarga no coincide con el SHA-256 oficial: $url"
    }
}
function Save-SignedCygwin([string]$path) {
    if (-not (Test-Path -LiteralPath $path)) {
        Write-Step 'Descargando instalador oficial de Cygwin...'
        Invoke-WebRequest 'https://cygwin.com/setup-x86_64.exe' -OutFile $path -UseBasicParsing
    }
    $signature = Get-AuthenticodeSignature -LiteralPath $path
    if ($signature.Status -ne 'Valid') { throw 'La firma digital del instalador de Cygwin no es valida.' }
}
function Save-ComposerInstaller([string]$path) {
    Write-Step 'Descargando y verificando el instalador oficial de Composer...'
    $expected = ((Invoke-WebRequest 'https://composer.github.io/installer.sig' -UseBasicParsing).Content).Trim()
    Invoke-WebRequest 'https://getcomposer.org/installer' -OutFile $path -UseBasicParsing
    $actual = (Get-FileHash -LiteralPath $path -Algorithm SHA384).Hash
    if ($actual -ne $expected) {
        Remove-Item -LiteralPath $path -Force
        throw 'La firma SHA-384 del instalador de Composer no coincide.'
    }
}

$status = Get-Status
$status | Format-List
if ($Comprobar) { return }

if ((Test-Path -LiteralPath 'C:\xampp') -and -not (Test-Path -LiteralPath $php)) {
    throw 'C:\xampp ya existe pero PHP no esta completo. Revisa esa instalacion antes de continuar.'
}
if ((Test-Path -LiteralPath $php) -and -not (Test-MinVersion $status.PHP ([version]'8.2'))) {
    throw 'La instalacion existente de PHP es menor que 8.2. No se reemplaza XAMPP automaticamente.'
}
if ((Test-Path -LiteralPath $mysqld) -and -not (Test-MinVersion $status.MariaDB ([version]'10.5'))) {
    throw 'La instalacion existente de MariaDB es menor que 10.5. Haz respaldo y migra XAMPP antes de continuar.'
}

New-Item -ItemType Directory -Path $downloads -Force | Out-Null
$xamppInstaller = Join-Path $downloads 'xampp-windows-x64-8.3.36-0-VC16-installer.exe'
$rrdZip = Join-Path $downloads 'rrdtool-1.8.0-x64_vcpkg.zip'
$cygwinInstaller = Join-Path $downloads 'setup-x86_64.exe'
$composerInstaller = Join-Path $downloads 'composer-setup.php'

# Etapa 1: descargar y validar todo lo que falta antes de ejecutar instaladores.
if (-not (Test-Path -LiteralPath $php)) {
    Save-Download 'https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.3.36/xampp-windows-x64-8.3.36-0-VC16-installer.exe/download' $xamppInstaller 'DD54667C0C379CC137A92889857BD809C24A619F18EACB09C7376EF914391BBF'
}
if ($status.RRDtool -eq 'Ausente') {
    Save-Download 'https://github.com/oetiker/rrdtool-1.x/releases/download/v1.8.0/rrdtool-1.8.0-x64_vcpkg.zip' $rrdZip 'F1FFB2926AA6045A93FBF0FE53A3BE1F34EA1D2C4D3667B3EEE5490E8ECECEB7'
}
if (-not $status.NetSNMP) { Save-SignedCygwin $cygwinInstaller }
if (-not $status.BibliotecasPHP) { Save-ComposerInstaller $composerInstaller }
Write-Step 'Descargas completas. Iniciando instalacion...'

# Etapa 2: instalar componentes sin tocar instalaciones preexistentes.
if (-not (Test-Path -LiteralPath $php)) {
    Write-Step 'Abriendo XAMPP. Completa su asistente en C:\xampp.'
    $process = Start-Process -FilePath $xamppInstaller -Wait -PassThru -WindowStyle Normal
    if ($process.ExitCode -ne 0 -or -not (Test-Path -LiteralPath $php)) {
        throw 'XAMPP no quedo instalado en C:\xampp. Completa el asistente y vuelve a ejecutar este archivo.'
    }
}
if ($status.RRDtool -eq 'Ausente') {
    New-Item -ItemType Directory -Path 'C:\rrdtool' -Force | Out-Null
    Expand-Archive -LiteralPath $rrdZip -DestinationPath 'C:\rrdtool' -Force
    $rrdFound = Get-ChildItem 'C:\rrdtool' -Filter 'rrdtool.exe' -Recurse | Select-Object -First 1 -ExpandProperty FullName
    if (-not $rrdFound) { throw 'No se encontro rrdtool.exe despues de extraer el archivo.' }
    Write-Step "RRDtool: $rrdFound"
}
if (-not (Test-Path -LiteralPath $snmp)) {
    Write-Step 'Instalando Net-SNMP mediante Cygwin...'
    $cygwinArgs = @('-q', '-n', '-N', '-R', 'C:\cygwin64', '-P', 'net-snmp-utils', '-s', 'https://mirrors.kernel.org/sourceware/cygwin/')
    $process = Start-Process -FilePath $cygwinInstaller -ArgumentList $cygwinArgs -Wait -PassThru -WindowStyle Hidden
    if ($process.ExitCode -ne 0 -or -not (Test-Path -LiteralPath $snmp)) {
        throw 'Cygwin/Net-SNMP no termino correctamente. Abre el instalador descargado y selecciona net-snmp-utils.'
    }
}
if (-not (Test-Path -LiteralPath (Join-Path $project 'vendor\autoload.php'))) {
    & $php $composerInstaller "--install-dir=$downloads" '--filename=composer.phar' '--quiet'
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo instalar Composer.' }
    Push-Location $project
    try {
        & $php (Join-Path $downloads 'composer.phar') install --no-dev --prefer-dist --no-interaction
        if ($LASTEXITCODE -ne 0) { throw 'Composer no pudo instalar las bibliotecas PHP.' }
    } finally { Pop-Location }
}

$webRoot = 'C:\xampp\htdocs\cacti'
if (Test-Path -LiteralPath $webRoot) {
    $link = Get-Item -LiteralPath $webRoot -Force
    $actual = if ($link.LinkType -eq 'Junction' -or $link.LinkType -eq 'SymbolicLink') { $link.Target } else { $link.FullName }
    if ([IO.Path]::GetFullPath([string]$actual).TrimEnd('\') -ne [IO.Path]::GetFullPath($project).TrimEnd('\')) {
        throw "$webRoot ya apunta a otra carpeta. No se reemplazo. Configura el sitio manualmente."
    }
} else {
    New-Item -ItemType Junction -Path $webRoot -Target $project | Out-Null
    Write-Step "Sitio enlazado: $webRoot"
}

Write-Step 'Herramientas preparadas. Configura la base de datos y include\config.php con credenciales propias.'
Write-Step 'Inicia Apache y MariaDB en XAMPP; termina el asistente web de Cacti.'
Start-Process 'http://localhost/cacti/'
Write-Step 'Cuando acabes el asistente web, vuelve a ejecutar este archivo para comprobar el entorno.'
