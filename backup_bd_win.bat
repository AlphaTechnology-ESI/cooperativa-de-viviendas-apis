@echo off
REM Script de backup para Windows CMD

REM Cargar variables desde .env
for /f "usebackq tokens=1,2 delims== " %%A in (".env") do (
    set "%%A=%%B"
)

REM Validar variables requeridas
if "%db%"=="" goto :faltan
if "%user%"=="" goto :faltan
if "%pass%"=="" goto :faltan
if "%host%"=="" goto :faltan
if "%port%"=="" goto :faltan

REM Configuración
set fecha=%date:~0,4%-%date:~5,2%-%date:~8,2%_%time:~0,2%-%time:~3,2%
set fecha=%fecha: =0%
set destino=backups

REM Crear directorio de backups si no existe
if not exist "%destino%" mkdir "%destino%"

REM Crear backup
mysqldump --single-transaction --set-gtid-purged=OFF -u %user% -p%pass% %db% -h %host% -P %port% > "%destino%/backup_%fecha%.sql"

REM Mantener solo las 2 últimas copias
for /f "skip=2 delims=" %%F in ('dir /b /o-d "%destino%\backup_*.sql"') do del "%destino%\%%F"

goto :fin

:faltan
echo Faltan variables requeridas en .env (db, user, pass, host, port). Abortando.
goto :fin

:fin
