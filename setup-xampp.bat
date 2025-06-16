@echo off
echo =====================================
echo  CRM EYM - Configuracion para XAMPP
echo =====================================
echo.

REM Generar clave de aplicacion
echo [1/4] Generando clave de aplicacion...
php artisan key:generate

REM Ejecutar migraciones
echo.
echo [2/4] Ejecutando migraciones...
php artisan migrate --force

REM Ejecutar seeders
echo.
echo [3/4] Cargando datos de prueba...
php artisan db:seed --force

REM Instalar dependencias Node.js
echo.
echo [4/4] Instalando dependencias frontend...
npm install

echo.
echo =====================================
echo  Configuracion completada!
echo =====================================
echo.
echo Para ejecutar el proyecto:
echo 1. Inicia XAMPP (Apache + MySQL)
echo 2. Crea la base de datos 'crm_eym' en phpMyAdmin
echo 3. Ejecuta: php artisan serve
echo 4. Abre: http://localhost:8000
echo.
echo Usuarios de prueba:
echo - admin@crm.local / password123
echo - juan.perez@crm.local / password123
echo.
pause 