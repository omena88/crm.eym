# CRM EYM - Sistema de Gestión de Relaciones con Clientes

Un sistema CRM completo desarrollado con **Laravel 11**, **Vue 3**, **Radix Vue** y **Tailwind CSS**.

## 🚀 Características

- **Gestión de Clientes**: CRUD completo con filtros avanzados
- **Sistema de Contactos**: Múltiples contactos por cliente con contacto principal
- **Gestión de Visitas**: Programación, seguimiento y gestión de estados
- **Dashboard Interactivo**: Métricas en tiempo real y gráficos
- **Sistema de Emails**: Notificaciones y templates personalizables
- **Autenticación**: Sistema completo con Laravel Breeze
- **Responsive**: Diseño moderno y adaptable

## 🛠️ Stack Tecnológico

- **Backend**: Laravel 11
- **Frontend**: Vue 3 + Composition API
- **UI Components**: Radix Vue
- **Estilos**: Tailwind CSS
- **Base de Datos**: MySQL
- **Autenticación**: Laravel Breeze
- **Iconos**: Lucide Vue

## 📋 Requisitos

- PHP 8.2 o superior
- Composer
- Node.js 18 o superior
- MySQL 8.0 o superior

## ⚡ Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd crm-eym
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Instalar dependencias Node.js**
```bash
npm install
```

4. **Configurar base de datos**
- Crear base de datos `crm_eym` en MySQL
- Configurar credenciales en `.env`

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate --seed
```

6. **Generar clave de aplicación**
```bash
php artisan key:generate
```

7. **Compilar assets**
```bash
npm run build
# o para desarrollo
npm run dev
```

8. **Servir la aplicación**
```bash
php artisan serve
```

## 👥 Usuarios de Prueba

El sistema incluye datos de prueba con los siguientes usuarios:

- **Admin**: admin@crm.local / password123
- **Usuario 1**: juan.perez@crm.local / password123
- **Usuario 2**: maria.rodriguez@crm.local / password123
- **Usuario 3**: carlos.mendez@crm.local / password123

## 📊 Datos de Prueba

- **8 Clientes** diversos con diferentes sectores y tamaños
- **12 Contactos** vinculados con contactos principales
- **9 Visitas** en diferentes estados (programadas, realizadas, canceladas)

## 🗂️ Estructura del Proyecto

```
crm-eym/
├── app/
│   ├── Http/Controllers/     # Controladores
│   └── Models/              # Modelos Eloquent
├── database/
│   ├── migrations/          # Migraciones
│   └── seeders/            # Seeders con datos de prueba
├── resources/
│   ├── js/                 # Vue.js components
│   └── views/              # Blade templates
└── routes/
    └── web.php             # Rutas web
```

## 🚀 Funcionalidades

### Dashboard
- Métricas de clientes, visitas y pipeline
- Gráficos de tendencias
- Actividades recientes
- Alertas y notificaciones

### Gestión de Clientes
- CRUD completo
- Filtros por estado, sector, tamaño
- Búsqueda avanzada
- Estadísticas por cliente

### Sistema de Contactos
- Múltiples contactos por cliente
- Gestión de contacto principal automática
- Información completa de contacto

### Gestión de Visitas
- Programación de visitas
- Estados: Programada, realizada, cancelada, vencida
- Filtros por fecha, tipo, prioridad
- Seguimiento de objetivos

### Sistema de Emails
- Templates predefinidos
- Emails personalizados
- Verificación de configuración
- Notificaciones automáticas

## 🎨 Diseño

El sistema utiliza un diseño moderno y limpio con:
- Interfaz responsive
- Componentes reutilizables
- Paleta de colores consistente
- UX optimizada para productividad

## 📱 Compatibilidad

- ✅ Desktop (Chrome, Firefox, Safari, Edge)
- ✅ Tablet
- ✅ Mobile

## 🔧 Desarrollo

Para desarrollo local:

```bash
# Terminal 1: Backend
php artisan serve

# Terminal 2: Frontend (hot reload)
npm run dev
```

## 📄 Licencia

Este proyecto está bajo la Licencia MIT.

## 🤝 Contribución

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Abre un Pull Request

## 📞 Soporte

Para soporte técnico o preguntas, contacta al equipo de desarrollo.

---

**CRM EYM** - Sistema completo de gestión de relaciones con clientes. 