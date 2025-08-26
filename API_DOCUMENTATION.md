# API de Recetas - Documentación

## Base URL
```
http://localhost:8000/api/v1
```

## Autenticación
La API utiliza Laravel Sanctum para la autenticación. Para las rutas protegidas, incluye el token en el header:
```
Authorization: Bearer {token}
```

## Endpoints

### 🔐 Autenticación

#### Registro de usuario
```http
POST /register
```

**Body:**
```json
{
    "name": "Juan Pérez",
    "username": "juanperez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "bio": "Amante de la cocina"
}
```

#### Login
```http
POST /login
```

**Body:**
```json
{
    "email": "juan@example.com",
    "password": "password123"
}
```

#### Obtener perfil del usuario
```http
GET /me
```
*Requiere autenticación*

#### Actualizar perfil
```http
PUT /profile
```
*Requiere autenticación*

**Body:**
```json
{
    "name": "Juan Pérez Actualizado",
    "username": "juanperez2",
    "email": "juan2@example.com",
    "bio": "Nueva bio",
    "avatar_url": "https://example.com/avatar.jpg"
}
```

#### Cambiar contraseña
```http
POST /change-password
```
*Requiere autenticación*

**Body:**
```json
{
    "current_password": "password123",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

#### Logout
```http
POST /logout
```
*Requiere autenticación*

### 📖 Recetas

#### Listar recetas públicas
```http
GET /recipes
```

**Query Parameters:**
- `search`: Buscar por título o descripción
- `difficulty`: Filtrar por dificultad (easy, medium, hard)
- `category_id`: Filtrar por categoría
- `max_time`: Tiempo máximo en minutos
- `sort_by`: Ordenar por (created_at, title, average_rating, favorites_count)
- `sort_order`: Orden (asc, desc)
- `per_page`: Elementos por página (default: 12)

#### Obtener receta específica
```http
GET /recipes/{slug}
```

#### Crear receta
```http
POST /recipes
```
*Requiere autenticación*

**Body:**
```json
{
    "title": "Tortilla Española",
    "description": "Tortilla tradicional española con patatas y huevos",
    "prep_minutes": 15,
    "cook_minutes": 25,
    "servings": 4,
    "difficulty": "medium",
    "is_public": true,
    "calories": 350,
    "categories": [1, 2],
    "ingredients": [
        {
            "ingredient_id": 1,
            "quantity": 200,
            "unit": "g",
            "note": "patatas"
        },
        {
            "ingredient_id": 2,
            "quantity": 4,
            "unit": "unidad",
            "note": "huevos grandes"
        }
    ],
    "steps": [
        {
            "step_number": 1,
            "instruction": "Pelar y cortar las patatas en rodajas finas"
        },
        {
            "step_number": 2,
            "instruction": "Freír las patatas en aceite de oliva"
        }
    ]
}
```

#### Actualizar receta
```http
PUT /recipes/{id}
```
*Requiere autenticación y ser propietario*

#### Eliminar receta
```http
DELETE /recipes/{id}
```
*Requiere autenticación y ser propietario*

#### Mis recetas
```http
GET /my-recipes
```
*Requiere autenticación*

#### Recetas favoritas
```http
GET /favorites
```
*Requiere autenticación*

#### Toggle favorito
```http
POST /recipes/{id}/favorite
```
*Requiere autenticación*

### 💬 Comentarios

#### Listar comentarios de una receta
```http
GET /recipes/{id}/comments
```

#### Crear comentario
```http
POST /recipes/{id}/comments
```
*Requiere autenticación*

**Body:**
```json
{
    "content": "¡Excelente receta! La hice y quedó perfecta."
}
```

#### Actualizar comentario
```http
PUT /comments/{id}
```
*Requiere autenticación y ser propietario*

#### Eliminar comentario
```http
DELETE /comments/{id}
```
*Requiere autenticación y ser propietario o dueño de la receta*

### ⭐ Valoraciones

#### Listar valoraciones de una receta
```http
GET /recipes/{id}/ratings
```

#### Crear valoración
```http
POST /recipes/{id}/ratings
```
*Requiere autenticación*

**Body:**
```json
{
    "rating": 5
}
```

#### Actualizar valoración
```http
PUT /ratings/{id}
```
*Requiere autenticación y ser propietario*

#### Eliminar valoración
```http
DELETE /ratings/{id}
```
*Requiere autenticación y ser propietario*

#### Mi valoración de una receta
```http
GET /recipes/{id}/my-rating
```
*Requiere autenticación*

### 📂 Categorías

#### Listar categorías
```http
GET /categories
```

#### Obtener categoría específica
```http
GET /categories/{id}
```

#### Crear categoría (Admin)
```http
POST /admin/categories
```
*Requiere autenticación*

**Body:**
```json
{
    "name": "Vegetariano"
}
```

#### Actualizar categoría (Admin)
```http
PUT /admin/categories/{id}
```
*Requiere autenticación*

#### Eliminar categoría (Admin)
```http
DELETE /admin/categories/{id}
```
*Requiere autenticación*

### 🥕 Ingredientes

#### Listar ingredientes
```http
GET /ingredients
```

**Query Parameters:**
- `search`: Buscar por nombre
- `per_page`: Elementos por página (default: 20)

#### Buscar ingredientes
```http
GET /ingredients/search?q=tomate
```

#### Obtener ingrediente específico
```http
GET /ingredients/{id}
```

#### Crear ingrediente (Admin)
```http
POST /admin/ingredients
```
*Requiere autenticación*

**Body:**
```json
{
    "name": "Tomate Cherry",
    "default_unit": "g"
}
```

#### Actualizar ingrediente (Admin)
```http
PUT /admin/ingredients/{id}
```
*Requiere autenticación*

#### Eliminar ingrediente (Admin)
```http
DELETE /admin/ingredients/{id}
```
*Requiere autenticación*

## Respuestas

### Formato de respuesta exitosa
```json
{
    "success": true,
    "message": "Operación exitosa",
    "data": {
        // Datos de la respuesta
    }
}
```

### Formato de respuesta de error
```json
{
    "success": false,
    "message": "Mensaje de error",
    "errors": {
        // Errores de validación (si aplica)
    }
}
```

### Códigos de estado HTTP
- `200`: OK
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error

## Ejemplos de uso

### 1. Registro y login
```bash
# Registro
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ana García",
    "username": "anagarcia",
    "email": "ana@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "ana@example.com",
    "password": "password123"
  }'
```

### 2. Crear una receta
```bash
curl -X POST http://localhost:8000/api/v1/create-recipe \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "title": "Pasta Carbonara",
    "description": "Pasta italiana con huevo, queso y panceta",
    "prep_minutes": 10,
    "cook_minutes": 15,
    "servings": 2,
    "difficulty": "easy",
    "categories": [2],
    "ingredients": [
      {
        "ingredient_id": 10,
        "quantity": 200,
        "unit": "g"
      }
    ],
    "steps": [
      {
        "step_number": 1,
        "instruction": "Cocinar la pasta en agua con sal"
      }
    ]
  }'
```

### 3. Buscar recetas
```bash
curl "http://localhost:8000/api/v1/recipes?search=pasta&difficulty=easy&sort_by=created_at&sort_order=desc"
```

## Notas importantes

1. **Autenticación**: Todas las rutas protegidas requieren el header `Authorization: Bearer {token}`
2. **Validación**: Los datos enviados son validados automáticamente
3. **Paginación**: Las listas están paginadas por defecto
4. **Relaciones**: Los modelos incluyen sus relaciones cuando es necesario
5. **Permisos**: Solo los propietarios pueden editar/eliminar sus recursos
6. **Búsqueda**: Las búsquedas son case-insensitive y usan LIKE
7. **Ordenamiento**: Se puede ordenar por múltiples campos
8. **Filtros**: Se pueden aplicar múltiples filtros simultáneamente

## Base de datos

La API está diseñada para trabajar con la estructura de base de datos proporcionada, que incluye:

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

## Configuración

1. Asegúrate de tener Laravel Sanctum configurado
2. Ejecuta las migraciones: `php artisan migrate`
3. Configura el archivo `.env` con los datos de tu base de datos
4. Genera la clave de aplicación: `php artisan key:generate`
5. Configura el storage: `php artisan storage:link`
