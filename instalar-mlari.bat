@echo off
setlocal
title Instalador MLARI
cd /d "%~dp0"
if not exist "%~dp0instalar-mlari.ps1" (
  echo Falta instalar-mlari.ps1 en la misma carpeta.
  pause
  exit /b 1
)
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0instalar-mlari.ps1" %*
set "RESULTADO=%ERRORLEVEL%"
if not "%RESULTADO%"=="0" echo Instalacion incompleta. Revisa el mensaje anterior.
echo.
pause
exit /b %RESULTADO%
