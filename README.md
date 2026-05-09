# 🍽️ Sistema POS & KDS para Restaurantes

Un sistema moderno, reactivo y escalable para la gestión integral de restaurantes. Diseñado con una arquitectura híbrida ("Islands of React" sobre Blade) que garantiza un rendimiento óptimo, SEO amigable y una experiencia de usuario (UX/UI) altamente dinámica.

---

## 🚀 Tecnologías Principales (Tech Stack)

*   **Backend:** [Laravel 12.x](https://laravel.com/) (PHP 8.2+)
*   **Frontend UI:** [React 18](https://react.dev/) + [Tailwind CSS 3](https://tailwindcss.com/)
*   **Bundler:** [Vite](https://vitejs.dev/) (con Code Splitting y Lazy Loading)
*   **Base de Datos:** MySQL / MariaDB
*   **Interacciones y Alertas:** SweetAlert2 & Alpine.js (para componentes ligeros)

---

## 📋 Requisitos del Sistema

Para ejecutar este proyecto en tu entorno local, asegúrate de tener instalados los siguientes componentes:

*   **PHP** >= 8.2 (extensiones: `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`)
*   **Composer** >= 2.0
*   **Node.js** >= 18.x y **npm** (o yarn/pnpm)
*   **MySQL** >= 8.0 o **MariaDB** >= 10.3

---

## 🛠️ Guía de Instalación Rápida

Sigue estos pasos secuenciales para levantar el proyecto desde cero en tu entorno de desarrollo local.

### 1. Clonar y Configurar el Entorno

```bash
# Instalar las dependencias de PHP
composer install

# Instalar las dependencias de Frontend (React/Tailwind)
npm install

# Copiar el archivo de entorno y generar la clave de la aplicación
cp .env.example .env
php artisan key:generate
```

### 2. Configuración de Base de Datos

Abre el archivo `.env` en la raíz del proyecto y configura tus credenciales locales de base de datos:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurante_db
DB_USERNAME=root
DB_PASSWORD=tu_contraseña_aqui
```

### 3. Enlace de Almacenamiento (Storage Link)

Requerido para que las imágenes (logotipos de la empresa, fotos de productos) sean accesibles públicamente:

```bash
php artisan storage:link
```

---

## 🗄️ Migraciones y Poblado de Datos (Seeding)

El proyecto cuenta con un **Database Seeder** diseñado a nivel de producción que inyecta datos de prueba coherentes para que puedas evaluar el sistema de inmediato (Usuarios, Mesas, Productos, Recetas, Gastos y Órdenes previas).

Ejecuta el siguiente comando para limpiar tu base de datos y reconstruirla con datos de prueba:

```bash
php artisan migrate:fresh --seed
```

> ⚠️ **Advertencia:** El flag `migrate:fresh` eliminará todas las tablas existentes en la base de datos antes de reconstruirlas. **No utilices este comando en un entorno de producción.**

### 🔑 Credenciales de Acceso por Defecto

El seeder generará tres usuarios estratégicos para que pruebes los diferentes niveles de acceso y permisos:

| Rol | Correo Electrónico (Email) | Contraseña (Password) |
| :--- | :--- | :--- |
| **Administrador** | `admin@admin.com` | `password` |
| **Cajero Principal** | `cajero@restaurante.com` | `password` |
| **Mozo / Mesero** | `mozo@restaurante.com` | `password` |

---

## 💻 Ejecución del Proyecto (Entorno de Desarrollo)

Dado que este proyecto utiliza Vite para compilar React en tiempo real, necesitas ejecutar **dos procesos simultáneos** en tu terminal.

**Terminal 1 (Backend de Laravel):**
```bash
php artisan serve
```
*El servidor de PHP se levantará típicamente en `http://127.0.0.1:8000`*

**Terminal 2 (Frontend HMR con Vite):**
```bash
npm run dev
```
*Este comando se encarga del Hot Module Replacement (HMR) para React y Tailwind CSS.*

---

## 📦 Despliegue a Producción (Build)

Cuando el sistema esté listo para ser subido a un servidor de producción (VPS, cPanel, etc.), debes compilar los assets de React/Tailwind.

```bash
npm run build
```
*Este comando minificará el código fuente, aplicará Code Splitting y generará los archivos estáticos optimizados en el directorio `public/build`.*

Luego, solo necesitarás apuntar el Document Root de tu servidor Apache/Nginx a la carpeta `/public` del proyecto.
