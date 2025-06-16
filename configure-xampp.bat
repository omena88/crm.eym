@echo off
echo ===============================================
echo  CRM EYM - Configuracion Automatica para XAMPP
echo ===============================================
echo.

REM Crear backup del .env actual
if exist .env (
    copy .env .env.backup
    echo [INFO] Backup del .env creado como .env.backup
)

REM Crear nuevo .env configurado para XAMPP
echo [1/5] Configurando archivo .env para XAMPP...
echo APP_NAME="CRM EYM"> .env
echo APP_ENV=local>> .env
echo APP_KEY=>> .env
echo APP_DEBUG=true>> .env
echo APP_TIMEZONE=UTC>> .env
echo APP_URL=http://localhost:8000>> .env
echo.>> .env
echo APP_LOCALE=es>> .env
echo APP_FALLBACK_LOCALE=en>> .env
echo APP_FAKER_LOCALE=es_ES>> .env
echo.>> .env
echo LOG_CHANNEL=stack>> .env
echo LOG_STACK=single>> .env
echo LOG_LEVEL=debug>> .env
echo.>> .env
echo DB_CONNECTION=mysql>> .env
echo DB_HOST=127.0.0.1>> .env
echo DB_PORT=3306>> .env
echo DB_DATABASE=crm_eym>> .env
echo DB_USERNAME=root>> .env
echo DB_PASSWORD=>> .env
echo.>> .env
echo SESSION_DRIVER=database>> .env
echo SESSION_LIFETIME=120>> .env
echo.>> .env
echo CACHE_STORE=database>> .env
echo QUEUE_CONNECTION=database>> .env
echo.>> .env
echo MAIL_MAILER=log>> .env
echo MAIL_HOST=127.0.0.1>> .env
echo MAIL_PORT=2525>> .env
echo MAIL_USERNAME=null>> .env
echo MAIL_PASSWORD=null>> .env
echo MAIL_ENCRYPTION=null>> .env
echo MAIL_FROM_ADDRESS="noreply@crm-eym.local">> .env
echo MAIL_FROM_NAME="${APP_NAME}">> .env

REM Generar clave de aplicacion
echo.
echo [2/5] Generando clave de aplicacion...
php artisan key:generate

REM Limpiar cache
echo.
echo [3/5] Limpiando cache...
php artisan config:clear
php artisan cache:clear

echo.
echo [4/5] Verificando configuracion de base de datos...
php artisan config:show database | findstr "default"
php artisan config:show database | findstr "mysql.*database"

echo.
echo [5/5] Verificando migraciones disponibles...
php artisan migrate:status

echo.
echo ===============================================
echo  Configuracion completada!
echo ===============================================
echo.
echo SIGUIENTE PASO:
echo 1. Abre phpMyAdmin: http://localhost/phpmyadmin
echo 2. Crea la base de datos 'crm_eym'
echo 3. Ejecuta: php artisan migrate --seed
echo 4. Ejecuta: php artisan serve
echo.
pause 