# CRM EYM - Sistema de Gestión de Relaciones con Clientes

Un sistema CRM completo desarrollado con Laravel 11 y Vue.js 3, utilizando Inertia.js para una experiencia de aplicación de página única (SPA).

## 🚀 Características

### Dashboard Interactivo
- Métricas de ventas en tiempo real
- Gráficos de desempeño de visitas (planificadas vs realizadas)
- Alertas y notificaciones inteligentes
- Panel de acciones rápidas

### Gestión de Clientes
- CRUD completo de clientes
- Búsqueda y filtrado avanzado
- Exportación e importación de datos
- Estados de cliente (Pendiente, Visitado, Cotizado, etc.)
- Sectores industriales personalizables

### Gestión de Contactos
- Múltiples contactos por cliente
- Designación de contacto principal
- Información completa (email, teléfono, puesto)

### Sistema de Visitas
- Planificación semanal de visitas
- Estados: Pendiente, Programada, Realizada, Cancelada
- Tipos: Comercial, Técnica, Seguimiento, Postventa
- Sistema de aprobación por roles

### Control de Acceso por Roles
- **Administrador**: Acceso completo al sistema
- **Gerente**: Supervisión y aprobación de visitas
- **Vendedor**: Gestión de clientes y planificación de visitas

## 🛠️ Tecnologías Utilizadas

- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 + Inertia.js
- **Estilos**: Tailwind CSS
- **Base de Datos**: SQLite (configurable)
- **Autenticación**: Laravel Breeze
- **Iconos**: Heroicons

## 📋 Requisitos del Sistema

- PHP >= 8.2
- Composer
- Node.js >= 18
- NPM o Yarn

## 🔧 Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/omena88/crm.eym.git
cd crm.eym
```

### 2. Instalar dependencias de PHP
```bash
composer install
```

### 3. Instalar dependencias de Node.js
```bash
npm install
```

### 4. Configuración del entorno
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 5. Configurar base de datos
Edita el archivo `.env` con tu configuración de base de datos:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### 6. Ejecutar migraciones y seeders
```bash
# Crear base de datos y ejecutar migraciones
php artisan migrate

# Llenar con datos de ejemplo
php artisan db:seed
```

### 7. Compilar assets
```bash
# Desarrollo
npm run dev

# Producción
npm run build
```

### 8. Iniciar servidor
```bash
php artisan serve
```

## 👥 Usuarios de Prueba

Después de ejecutar los seeders, tendrás estos usuarios disponibles:

| Email | Contraseña | Rol |
|-------|------------|-----|
| admin@crm.com | password | Administrador |
| gerente@crm.com | password | Gerente |
| vendedor@crm.com | password | Vendedor |

## 📱 Uso del Sistema

### Para Vendedores
1. **Gestionar Clientes**: Crear, editar y administrar información de clientes
2. **Planificar Visitas**: Organizar visitas semanales y enviar para aprobación
3. **Registrar Resultados**: Marcar visitas como realizadas con comentarios

### Para Gerentes
1. **Supervisar Ventas**: Revisar métricas y desempeño del equipo
2. **Aprobar Visitas**: Validar planificaciones semanales de vendedores
3. **Analizar Datos**: Acceder a reportes y estadísticas avanzadas

### Para Administradores
1. **Gestión Completa**: Acceso total a todas las funcionalidades
2. **Administrar Usuarios**: Crear y gestionar cuentas de usuarios
3. **Configuración**: Personalizar categorías y estados del sistema

## 🗂️ Estructura del Proyecto

```
crm-eym/
├── app/
│   ├── Http/Controllers/          # Controladores
│   ├── Models/                    # Modelos Eloquent
│   └── Http/Middleware/           # Middleware personalizado
├── database/
│   ├── migrations/                # Migraciones de base de datos
│   └── seeders/                   # Datos de prueba
├── resources/
│   ├── js/
│   │   ├── Components/            # Componentes Vue reutilizables
│   │   ├── Layouts/               # Layouts de la aplicación
│   │   └── Pages/                 # Páginas Vue/Inertia
│   └── css/                       # Estilos Tailwind
└── routes/                        # Rutas de la aplicación
```

## 🚀 Despliegue

Para producción, asegúrate de:

1. **Configurar variables de entorno de producción**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
```

2. **Optimizar para producción**:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

3. **Configurar servidor web** (Apache/Nginx) para apuntar a la carpeta `public/`

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia [MIT](LICENSE).

## 📞 Soporte

Para soporte o preguntas, abre un [issue](https://github.com/omena88/crm.eym/issues) en GitHub.

---

**Desarrollado con ❤️ usando Laravel y Vue.js** 