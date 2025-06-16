@echo off
echo ==========================================
echo  CRM EYM - Configuracion SQLite (Rapida)
echo ==========================================
echo.

REM Backup del .env actual
if exist .env copy .env .env.backup.mysql

REM Configurar para SQLite
echo APP_NAME="CRM EYM"> .env
echo APP_ENV=local>> .env
echo APP_KEY=>> .env
echo APP_DEBUG=true>> .env
echo APP_TIMEZONE=UTC>> .env
echo APP_URL=http://localhost:8000>> .env
echo.>> .env
echo LOG_CHANNEL=stack>> .env
echo LOG_LEVEL=debug>> .env
echo.>> .env
echo DB_CONNECTION=sqlite>> .env
echo DB_DATABASE=C:\Users\PC\Desktop\Cursor\crm-eym\database\database.sqlite>> .env
echo.>> .env
echo CACHE_STORE=file>> .env
echo SESSION_DRIVER=file>> .env
echo QUEUE_CONNECTION=sync>> .env
echo.>> .env
echo MAIL_MAILER=log>> .env

REM Generar clave
echo [1/4] Generando clave de aplicacion...
php artisan key:generate

REM Limpiar cache
echo.
echo [2/4] Limpiando cache...
php artisan config:clear
php artisan cache:clear

REM Ejecutar migraciones
echo.
echo [3/4] Creando tablas...
php artisan migrate --force

REM Ejecutar seeders
echo.
echo [4/4] Cargando datos de prueba...
php artisan db:seed --force

echo.
echo ==========================================
echo  LISTO! Proyecto configurado con SQLite
echo ==========================================
echo.
echo Ejecuta: php artisan serve
echo Abre: http://localhost:8000
echo.
echo Usuarios:
echo - admin@crm.local / password123
echo - juan.perez@crm.local / password123
echo.
pause 