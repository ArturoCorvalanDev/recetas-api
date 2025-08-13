# 🍳 API de Recetas

Una API REST completa para gestionar recetas de cocina, construida con Laravel 12 y Laravel Sanctum.

## ✨ Características

- 🔐 **Autenticación completa** con Laravel Sanctum
- 📖 **Gestión de recetas** con ingredientes, pasos y categorías
- 💬 **Sistema de comentarios** en recetas
- ⭐ **Sistema de valoraciones** (1-5 estrellas)
- ❤️ **Sistema de favoritos** para usuarios
- 📂 **Categorización** de recetas
- 🥕 **Gestión de ingredientes** con búsqueda
- 📸 **Soporte para fotos** de recetas
- 🔍 **Búsqueda y filtros** avanzados
- 📱 **API RESTful** con documentación completa

## 🚀 Instalación

### Prerrequisitos

- PHP 8.2 o superior
- Composer
- MySQL/MariaDB
- Node.js (para assets)

### Pasos de instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd recetas-api
```

2. **Instalar dependencias**
```bash
composer install
npm install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
```

Edita el archivo `.env` con tu configuración de base de datos:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=recetas_app
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

4. **Generar clave de aplicación**
```bash
php artisan key:generate
```

5. **Ejecutar migraciones**
```bash
php artisan migrate
```

6. **Configurar storage**
```bash
php artisan storage:link
```

7. **Compilar assets (opcional)**
```bash
npm run build
```

8. **Iniciar servidor**
```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000/api/v1`

## 📊 Estructura de la Base de Datos

El sistema incluye las siguientes tablas:

- **users**: Usuarios del sistema
- **recipes**: Recetas
- **categories**: Categorías de recetas
- **ingredients**: Ingredientes
- **recipe_steps**: Pasos de las recetas
- **recipe_ingredients**: Relación muchos a muchos entre recetas e ingredientes
- **recipe_categories**: Relación muchos a muchos entre recetas y categorías
- **photos**: Fotos de las recetas
- **comments**: Comentarios de las recetas
- **ratings**: Valoraciones de las recetas
- **favorites**: Favoritos de los usuarios

## 🔧 Configuración de la Base de Datos

Ejecuta el siguiente SQL para crear la base de datos y las tablas:

```sql
-- Base de datos
CREATE DATABASE IF NOT EXISTS recetas_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recetas_app;

-- Usuarios
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(255) NULL,
    bio TEXT NULL,
    email_verified_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Recetas
CREATE TABLE recipes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NULL,
    prep_minutes SMALLINT UNSIGNED NULL,
    cook_minutes SMALLINT UNSIGNED NULL,
    servings SMALLINT UNSIGNED NULL,
    difficulty ENUM('easy','medium','hard') DEFAULT 'easy',
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    calories INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_recipes_user (user_id),
    FULLTEXT INDEX ft_title_description (title, description),
    CONSTRAINT fk_recipes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Pasos
CREATE TABLE recipe_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id BIGINT UNSIGNED NOT NULL,
    step_number INT UNSIGNED NOT NULL,
    instruction TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_steps_recipe (recipe_id),
    CONSTRAINT fk_steps_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Ingredientes
CREATE TABLE ingredients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    default_unit VARCHAR(20) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Ingredientes por receta
CREATE TABLE recipe_ingredients (
    recipe_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(8,2) NULL,
    unit VARCHAR(20) NULL,
    note VARCHAR(255) NULL,
    PRIMARY KEY (recipe_id, ingredient_id),
    CONSTRAINT fk_ri_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ri_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Categorías
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    slug VARCHAR(80) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categorías por receta
CREATE TABLE recipe_categories (
    recipe_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (recipe_id, category_id),
    CONSTRAINT fk_rc_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    CONSTRAINT fk_rc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Fotos
CREATE TABLE photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    path VARCHAR(255) NOT NULL,
    is_cover TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_photos_recipe (recipe_id),
    CONSTRAINT fk_photos_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    CONSTRAINT fk_photos_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Comentarios
CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_comments_recipe (recipe_id),
    INDEX idx_comments_user (user_id),
    CONSTRAINT fk_comments_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Valoraciones
CREATE TABLE ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rating_user_recipe (recipe_id, user_id),
    INDEX idx_ratings_recipe (recipe_id),
    CONSTRAINT fk_ratings_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ratings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Favoritos
CREATE TABLE favorites (
    user_id BIGINT UNSIGNED NOT NULL,
    recipe_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, recipe_id),
    INDEX idx_favorites_recipe (recipe_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

## 📚 Documentación de la API

La documentación completa de la API está disponible en el archivo [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

### Endpoints principales:

- **Autenticación**: `/register`, `/login`, `/logout`
- **Recetas**: `/recipes` (CRUD completo)
- **Comentarios**: `/recipes/{id}/comments`
- **Valoraciones**: `/recipes/{id}/ratings`
- **Categorías**: `/categories`
- **Ingredientes**: `/ingredients`

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Ejecutar tests con coverage
php artisan test --coverage
```

## 📦 Estructura del Proyecto

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── AuthController.php
│           ├── RecipeController.php
│           ├── CommentController.php
│           ├── RatingController.php
│           ├── CategoryController.php
│           └── IngredientController.php
├── Models/
│   ├── User.php
│   ├── Recipe.php
│   ├── Category.php
│   ├── Ingredient.php
│   ├── RecipeStep.php
│   ├── Photo.php
│   ├── Comment.php
│   └── Rating.php
└── ...
```

## 🔒 Seguridad

- Autenticación con Laravel Sanctum
- Validación de datos en todos los endpoints
- Autorización basada en roles (propietario de recursos)
- Protección CSRF
- Sanitización de inputs

## 🚀 Despliegue

### Producción

1. Configurar variables de entorno para producción
2. Optimizar la aplicación:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
3. Configurar el servidor web (Apache/Nginx)
4. Configurar SSL/TLS

### Docker (opcional)

```bash
# Construir imagen
docker build -t recetas-api .

# Ejecutar contenedor
docker run -p 8000:8000 recetas-api
```

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Si tienes alguna pregunta o problema, por favor abre un issue en el repositorio.

## 🙏 Agradecimientos

- Laravel Framework
- Laravel Sanctum
- Comunidad de Laravel

---

**¡Disfruta cocinando! 🍳**
