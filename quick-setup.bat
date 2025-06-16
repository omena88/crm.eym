@echo off
echo =============================================
echo  CRM EYM - Configuracion Rapida de Base de Datos
echo =============================================
echo.
echo IMPORTANTE: Antes de continuar, asegurate de que:
echo 1. XAMPP este corriendo (Apache + MySQL)
echo 2. Hayas creado la base de datos 'crm_eym' en phpMyAdmin
echo.
pause
echo.

echo [1/3] Ejecutando migraciones...
php artisan migrate --force

echo.
echo [2/3] Cargando datos de prueba...
php artisan db:seed --force

echo.
echo [3/3] Limpiando cache...
php artisan config:clear
php artisan cache:clear

echo.
echo =============================================
echo  Base de datos configurada correctamente!
echo =============================================
echo.
echo Ahora puedes ejecutar:
echo php artisan serve
echo.
echo Y abrir: http://localhost:8000
echo.
echo Usuarios de prueba:
echo - admin@crm.local / password123
echo - juan.perez@crm.local / password123
echo.
pause 