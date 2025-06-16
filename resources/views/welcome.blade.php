<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM EYM - Sistema de Gestión</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        
        .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            min-width: 120px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e9ecef;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .feature {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: left;
        }
        
        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .feature h3 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .feature p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .status {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #c3e6cb;
        }
        
        .status strong {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .version-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🏢</div>
        <h1>CRM EYM</h1>
        <p class="subtitle">Sistema de Gestión de Relaciones con Clientes</p>
        
        <div class="status">
            <strong>✅ Sistema Funcionando Correctamente</strong>
            Backend Laravel configurado con SQLite y datos de prueba cargados.
        </div>
        
        <div class="buttons">
            <a href="/login" class="btn btn-primary">Iniciar Sesión</a>
            <a href="/register" class="btn btn-secondary">Registrarse</a>
        </div>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">👥</div>
                <h3>Gestión de Clientes</h3>
                <p>CRUD completo con 8 clientes de prueba</p>
            </div>
            <div class="feature">
                <div class="feature-icon">📞</div>
                <h3>Contactos</h3>
                <p>12 contactos vinculados listos</p>
            </div>
            <div class="feature">
                <div class="feature-icon">📅</div>
                <h3>Visitas</h3>
                <p>9 visitas programadas y realizadas</p>
            </div>
            <div class="feature">
                <div class="feature-icon">📊</div>
                <h3>Dashboard</h3>
                <p>Métricas y estadísticas en tiempo real</p>
            </div>
        </div>
        
        <div class="version-info">
            <strong>Usuarios de Prueba:</strong><br>
            • admin@crm.local / password123<br>
            • juan.perez@crm.local / password123<br>
            • maria.rodriguez@crm.local / password123<br>
            <br>
            <small>Laravel {{ app()->version() }} • PHP {{ PHP_VERSION }}</small>
        </div>
    </div>
</body>
</html> 