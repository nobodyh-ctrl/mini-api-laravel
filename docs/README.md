# Mini API – Jugadores del club

API REST hecha con **Laravel 12** que expone la información de los jugadores de un club. No tiene frontend: solo endpoints JSON documentados con **Swagger**.

Este documento explica los conceptos básicos, cómo levantar el proyecto, cómo funcionan las migraciones, qué pasa cuando se llama a la API y qué hace cada clase.

---

## Índice

1. [Conceptos básicos](#1-conceptos-básicos)
2. [Estructura del proyecto](#2-estructura-del-proyecto)
3. [Cómo levantar el proyecto](#3-cómo-levantar-el-proyecto)
4. [Migraciones](#4-migraciones)
5. [Seeders y factories: los datos de prueba](#5-seeders-y-factories-los-datos-de-prueba)
6. [Qué pasa cuando se llama a la API](#6-qué-pasa-cuando-se-llama-a-la-api)
7. [Las clases que intervienen](#7-las-clases-que-intervienen)
8. [Eloquent, el ORM de Laravel](#8-eloquent-el-orm-de-laravel)
9. [Swagger](#9-swagger)
10. [Comandos útiles](#10-comandos-útiles)

---

## 1. Conceptos básicos

### API REST

Una **API** (*Application Programming Interface*) es un programa que expone datos o funciones para que otros programas los usen. En vez de mostrar pantallas, responde con datos, en este caso en formato **JSON**.

**REST** es un estilo de diseño de APIs sobre HTTP con estas convenciones:

- Cada **recurso** (por ejemplo, los jugadores) tiene una **URL**: `/api/jugadores`.
- El **método HTTP** indica la acción:

  | Método | Acción | Ejemplo |
  |---|---|---|
  | `GET` | Leer | `GET /api/jugadores` → listar jugadores |
  | `POST` | Crear | `POST /api/jugadores` → crear un jugador |
  | `PUT` / `PATCH` | Modificar | `PUT /api/jugadores/5` → modificar el jugador 5 |
  | `DELETE` | Borrar | `DELETE /api/jugadores/5` → borrar el jugador 5 |

- El **código de estado** HTTP indica el resultado: `200` OK, `201` creado, `404` no encontrado, `422` datos inválidos, `500` error del servidor.

Esta API por ahora implementa `GET` (listar y ver un jugador) y `POST` (crear un jugador).

### Endpoint

Es la combinación de **método HTTP + URL** que atiende la API. Por ejemplo, `GET /api/jugadores/{id}` es un endpoint.

### Framework

Es un conjunto de herramientas y reglas que resuelve las tareas repetitivas de una aplicación: recibir requests, conectarse a la base, armar respuestas, etc. **Laravel** es un framework de PHP. Uno escribe solo la lógica propia (qué datos devolver) y Laravel se encarga del resto.

### Patrón MVC

Laravel organiza el código según el patrón **MVC** (*Model – View – Controller*), que separa responsabilidades:

| Capa | Responsabilidad | En este proyecto |
|---|---|---|
| **Model** (modelo) | Representa los datos y habla con la base | `Jugador` |
| **View** (vista) | Lo que ve el usuario | No hay vistas HTML; la "vista" es el JSON que arma `JugadorResource` |
| **Controller** (controlador) | Recibe la request, coordina y devuelve la respuesta | `JugadorController` |

La ventaja es que cada parte tiene una sola tarea: el controller no sabe escribir SQL y el modelo no sabe armar JSON.

### Ruta

Es la regla que asocia un endpoint con el código que lo atiende. "Cuando llegue `GET /api/jugadores`, ejecutá el método `index` de `JugadorController`". En Laravel están en `routes/api.php`.

### Controller

Es la clase que **recibe una request y devuelve una respuesta**. Cada método público (llamado *acción*) atiende un endpoint. Su trabajo es coordinar: pedirle los datos al modelo y devolverlos en el formato correcto. Un buen controller tiene poca lógica.

### Modelo

Es la clase que **representa una tabla** de la base de datos. Cada instancia del modelo es una fila. A través del modelo se consulta, crea, modifica y borra información sin escribir SQL.

### ORM

Un **ORM** (*Object-Relational Mapping*) es una capa que traduce entre **objetos** del lenguaje de programación y **tablas** de una base relacional.

Sin ORM se escribe SQL a mano y se recibe un array de valores sueltos:

```php
$filas = DB::select('SELECT * FROM jugadores WHERE posicion = ?', ['Arquero']);
```

Con ORM se trabaja con objetos y el ORM genera el SQL:

```php
$arqueros = Jugador::where('posicion', 'Arquero')->get();
$arqueros[0]->nombre;   // cada resultado es un objeto Jugador
```

Ventajas:
- Menos SQL repetitivo.
- Los valores se convierten al tipo correcto (fechas, números).
- Protección contra *SQL injection*.
- Se puede cambiar de motor de base de datos (MySQL, PostgreSQL, SQLite) sin tocar el código.

Laravel trae su propio ORM llamado **Eloquent** (ver [sección 8](#8-eloquent-el-orm-de-laravel)).

### Resource (DTO)

Un **DTO** (*Data Transfer Object*) es un objeto que define **qué datos se envían** al cliente, separado de cómo están guardados en la base. En Laravel se llaman **API Resources**.

Sirven para no exponer columnas internas (como `created_at`) y para que la respuesta de la API no cambie si se modifica la estructura de la base.

### Middleware

Es código que se ejecuta **antes y/o después** de que la request llegue al controller. Las requests pasan por una cadena de middleware, como por filtros. Se usan para tareas comunes a muchas rutas: autenticación, logs, limitar la cantidad de requests, recortar espacios de los datos recibidos, etc.

### Inyección de dependencias

Es una técnica en la que una clase **no crea por sí misma los objetos que necesita: los recibe**. Laravel tiene un **contenedor de servicios** que crea los objetos y se los pasa a los métodos según el tipo de cada parámetro. Por eso el método `show(Jugador $jugador)` recibe un jugador sin que nadie lo construya explícitamente.

### Migración

Es un archivo que describe un **cambio en la estructura de la base**: crear una tabla, agregar una columna, etc. Permite versionar la base junto con el código, de modo que cualquiera que clone el proyecto pueda recrear la misma estructura con un comando (ver [sección 4](#4-migraciones)).

### Seeder y factory

- Un **seeder** carga datos iniciales en la base.
- Una **factory** es una receta para generar registros de prueba con datos inventados.

Ver [sección 5](#5-seeders-y-factories-los-datos-de-prueba).

### Swagger / OpenAPI

**OpenAPI** es un formato estándar para describir una API: qué endpoints tiene, qué parámetros reciben y qué responden. **Swagger UI** es una página web que lee esa descripción y muestra una documentación interactiva desde donde se pueden probar los endpoints (ver [sección 9](#9-swagger)).

### Composer y Artisan

- **Composer** es el gestor de paquetes de PHP. Lee `composer.json` y descarga las librerías en la carpeta `vendor/`.
- **Artisan** es la herramienta de línea de comandos de Laravel. Con `php artisan ...` se corren migraciones, se crean clases, se levanta el servidor, etc. Para ver todos los comandos: `php artisan list`.

### Archivo `.env`

Contiene la **configuración que cambia según el entorno**: datos de conexión a la base, modo debug, claves. No se sube al repositorio porque tiene contraseñas. Cada desarrollador tiene el suyo, armado a partir de `.env.example`.

---

## 2. Estructura del proyecto

Solo se listan los archivos relevantes:

```
mini-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php              ← clase base (lleva la info general de Swagger)
│   │   │   └── Api/JugadorController.php   ← endpoints de jugadores
│   │   ├── Requests/
│   │   │   └── StoreJugadorRequest.php     ← reglas de validación para crear jugadores
│   │   └── Resources/
│   │       └── JugadorResource.php         ← define el JSON de salida
│   ├── Models/
│   │   └── Jugador.php                     ← modelo de la tabla jugadores
│   └── Providers/
│       └── AppServiceProvider.php          ← registro de servicios al arrancar la app
├── bootstrap/
│   └── app.php                             ← arma la aplicación: rutas, middleware
├── config/                                 ← configuración (base de datos, swagger, etc.)
├── database/
│   ├── factories/JugadorFactory.php        ← receta para generar jugadores falsos
│   ├── migrations/                         ← cambios de estructura de la base
│   └── seeders/DatabaseSeeder.php          ← carga de datos iniciales
├── public/
│   └── index.php                           ← punto de entrada de toda request HTTP
├── routes/
│   ├── api.php                             ← rutas de la API (prefijo /api automático)
│   └── web.php                             ← solo redirige "/" a la documentación
├── storage/api-docs/api-docs.json          ← JSON de Swagger generado
├── .env                                    ← variables de entorno (NO se sube al repo)
├── .env.example                            ← plantilla del .env
└── composer.json                           ← dependencias PHP
```

---

## 3. Cómo levantar el proyecto

### Requisitos

| Herramienta | Versión | Para qué |
|---|---|---|
| PHP | 8.2 o superior | Ejecutar Laravel (viene con XAMPP) |
| Composer | 2.x | Instalar las dependencias |
| MySQL / MariaDB | cualquiera reciente | Base de datos (viene con XAMPP) |

Para verificar que están instalados:

```bash
php -v
composer --version
```

### Paso a paso

**1. Instalar las dependencias.** Lee `composer.json` y descarga todo en la carpeta `vendor/`.

```bash
composer install
```

**2. Crear el archivo `.env`** a partir de la plantilla.

```bash
cp .env.example .env
```

**3. Generar la clave de la aplicación.** Completa `APP_KEY` en el `.env`; Laravel la usa para encriptar.

```bash
php artisan key:generate
```

**4. Configurar la base de datos.** Crear una base vacía en MySQL (por ejemplo desde phpMyAdmin) y completar en el `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini-api
DB_USERNAME=root
DB_PASSWORD=
```

**5. Ejecutar las migraciones y cargar los datos de prueba.** Crea las tablas y genera 25 jugadores.

```bash
php artisan migrate --seed
```

**6. Generar la documentación de Swagger.**

```bash
php artisan l5-swagger:generate
```

**7. Levantar el servidor.**

```bash
php artisan serve
```

Queda disponible en `http://localhost:8000`:

| URL | Qué es |
|---|---|
| `http://localhost:8000/api/jugadores` | Listado de jugadores |
| `http://localhost:8000/api/jugadores/1` | Un jugador por ID |
| `POST http://localhost:8000/api/jugadores` | Crear un jugador (ver [Crear un jugador](#crear-un-jugador-post)) |
| `http://localhost:8000/api/documentation` | Swagger UI |
| `http://localhost:8000/up` | Health check |

> **Atajo:** `composer setup` ejecuta los pasos 1, 2, 3, 5 (sin el seed) y 6 de una vez. Hay que tener la base creada y el `.env` configurado antes.

---

## 4. Migraciones

### Qué son

Archivos PHP que describen cambios en la estructura de la base. Cada migración tiene dos métodos:

```php
public function up(): void      // aplicar el cambio
{
    Schema::create('jugadores', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->unsignedTinyInteger('numero_camiseta')->unique();
        $table->timestamps();   // crea created_at y updated_at
    });
}

public function down(): void    // deshacer el cambio
{
    Schema::dropIfExists('jugadores');
}
```

### Cuándo se ejecutan

**Nunca se ejecutan solas.** Las corrés a mano con `php artisan migrate`. Ni levantar el servidor ni que llegue una request ejecuta migraciones.

### Cómo sabe Laravel cuáles faltan

Laravel crea automáticamente una tabla llamada **`migrations`**, donde anota qué archivos ya ejecutó y en qué lote (*batch*).

Al correr `php artisan migrate`:

1. Lista los archivos de `database/migrations/` **ordenados por nombre**. Por eso empiezan con fecha y hora (`2026_10_02_000001_...`).
2. Los compara con los que ya figuran en la tabla `migrations`.
3. Ejecuta el `up()` de los que faltan.
4. Los registra en `migrations`, todos con el mismo número de batch.

Si lo corrés dos veces seguidas, la segunda no hace nada.

### Comandos

| Comando | Qué hace |
|---|---|
| `php artisan make:migration create_equipos_table` | Crea una migración nueva |
| `php artisan migrate` | Corre las migraciones pendientes |
| `php artisan migrate:status` | Muestra cuáles corrieron y cuáles faltan |
| `php artisan migrate:rollback` | Deshace el **último batch** completo (ejecuta los `down()`) |
| `php artisan migrate:rollback --step=1` | Deshace solo la última migración |
| `php artisan migrate:fresh` | Borra **todas** las tablas y corre todo de cero |
| `php artisan migrate:fresh --seed` | Lo mismo y además carga los datos de prueba |

> ⚠️ **`migrate:fresh` hace DROP de todas las tablas de la base configurada en el `.env`.** Usalo solo en desarrollo y revisá antes a qué base apunta. En producción se usa siempre `php artisan migrate` a secas.

### Cómo agregar un cambio a una tabla existente

No se edita una migración que ya corrió: se crea una nueva.

```bash
php artisan make:migration add_altura_to_jugadores_table
```

```php
public function up(): void
{
    Schema::table('jugadores', function (Blueprint $table) {
        $table->decimal('altura', 3, 2)->nullable();
    });
}

public function down(): void
{
    Schema::table('jugadores', function (Blueprint $table) {
        $table->dropColumn('altura');
    });
}
```

---

## 5. Seeders y factories: los datos de prueba

Los 25 jugadores de la base no se cargaron a mano. Los generan tres piezas encadenadas.

### El seeder: `database/seeders/DatabaseSeeder.php`

Es el punto de entrada para cargar datos. Se ejecuta con `--seed` o con `php artisan db:seed`.

```php
public function run(): void
{
    Jugador::factory(25)->create();
}
```

Se lee así: "pedile a la factory de `Jugador` que fabrique 25 y guardalos en la base".

### La factory: `database/factories/JugadorFactory.php`

Es la receta para armar **un** jugador falso. `factory(25)` la ejecuta 25 veces.

```php
public function definition(): array
{
    return [
        'nombre' => fake()->firstName('male'),
        'apellido' => fake()->lastName(),
        'fecha_nacimiento' => fake()->dateTimeBetween('-38 years', '-17 years')->format('Y-m-d'),
        'posicion' => fake()->randomElement(['Arquero', 'Defensor', 'Mediocampista', 'Delantero']),
        'numero_camiseta' => fake()->unique()->numberBetween(1, 99),
        'nacionalidad' => fake()->randomElement(['Argentina', 'Uruguay', 'Paraguay', 'Chile', 'Colombia']),
    ];
}
```

### Faker: `fake()`

Es una librería que genera datos aleatorios realistas:

- `firstName()` / `lastName()`: nombres de una lista. Salen en inglés porque `APP_FAKER_LOCALE=en_US`; con `es_AR` saldrían en español.
- `dateTimeBetween(...)`: una fecha al azar, para que el jugador tenga entre 17 y 38 años.
- `randomElement([...])`: elige un valor de la lista.
- `unique()->numberBetween(1, 99)`: evita repetir el número de camiseta. Es necesario porque la columna es `unique`; si se repitiera, el `INSERT` fallaría.

### La cadena completa

```
php artisan migrate --seed
        │
        ├─ corre las migraciones → crea la tabla "jugadores"
        │
        └─ DatabaseSeeder::run()
               └─ Jugador::factory(25)->create()
                      └─ 25 veces: JugadorFactory::definition()   ← Faker inventa los datos
                                   └─ INSERT INTO jugadores (...)   ← Eloquent, a través del modelo
```

`Jugador::factory()` existe gracias al `use HasFactory;` del modelo, que conecta el modelo con su factory.

### Para tener en cuenta

- Son **datos de prueba**. Cada `migrate:fresh --seed` genera 25 jugadores distintos.
- `php artisan db:seed` solo **agrega** datos, sin borrar. Si ya hay jugadores, puede fallar por números de camiseta repetidos.
- Para cargar los jugadores reales, se escribe un seeder con los datos a mano, sin Faker.

---

## 6. Qué pasa cuando se llama a la API

### Recorrido de `GET /api/jugadores/5`

```
Cliente (navegador, Postman, frontend)
   │  GET /api/jugadores/5
   ▼
1. public/index.php           ← punto de entrada
   ▼
2. bootstrap/app.php          ← arma la aplicación y carga los providers
   ▼
3. Middleware                 ← filtros previos
   ▼
4. Router (routes/api.php)    ← elige JugadorController@show
   ▼
5. Route model binding        ← SELECT * FROM jugadores WHERE id = 5  (si no existe → 404)
   ▼
6. JugadorController::show()  ← recibe el jugador ya cargado
   ▼
7. JugadorResource            ← arma el JSON
   ▼
8. Respuesta HTTP 200         ← {"data": {...}}
```

**1. Punto de entrada.** El servidor web (`php artisan serve`, o Apache en XAMPP) recibe la request y ejecuta `public/index.php`. Todas las requests pasan por este archivo.

**2. Arranque de la aplicación.** `bootstrap/app.php` arma la aplicación: registra las rutas, los middleware y los *service providers* (clases que preparan servicios al arrancar, como `AppServiceProvider`). PHP levanta la aplicación de cero **en cada request** y la descarta al terminar.

**3. Middleware.** La request pasa por los middleware globales: validar el tamaño del body, recortar espacios en los textos recibidos, convertir strings vacíos en `null`, etc. Las rutas de `api.php` usan el grupo `api`, que no tiene sesiones ni cookies porque una API REST no las necesita.

**4. Router.** Busca en `routes/api.php` una ruta que coincida con el método y la URL. Laravel agrega automáticamente el prefijo `/api` a todas las rutas de ese archivo.

```php
Route::get('/jugadores', [JugadorController::class, 'index']);
Route::get('/jugadores/{jugador}', [JugadorController::class, 'show']);
```

`GET /api/jugadores/5` coincide con la segunda ruta, y `{jugador}` toma el valor `5`.

**5. Route model binding.** El parámetro de la ruta se llama `{jugador}` y el método del controller recibe `Jugador $jugador`. Como los nombres coinciden y el tipo es un modelo, Laravel busca el registro solo:

```sql
SELECT * FROM jugadores WHERE id = 5 LIMIT 1
```

Si no lo encuentra, corta acá y responde **404**, sin llegar al controller.

**6. Controller.** Se ejecuta el método, que recibe el jugador ya cargado:

```php
public function show(Jugador $jugador): JugadorResource
{
    return new JugadorResource($jugador);
}
```

En `index()` la consulta se escribe explícitamente:

```php
public function index(): AnonymousResourceCollection
{
    return JugadorResource::collection(Jugador::orderBy('numero_camiseta')->get());
}
```

**7. Resource.** Laravel llama a `JugadorResource::toArray()`, que elige qué campos se devuelven y con qué formato, y lo envuelve en `{"data": ...}`.

**8. Respuesta.** Se convierte a JSON, vuelve a pasar por los middleware de salida y se envía al cliente con código **200**.

> **Errores en JSON:** en `bootstrap/app.php` está configurado que toda ruta que empiece con `api/` responda los errores (404, 422, 500) en JSON, aunque el cliente no mande el header `Accept: application/json`.

### Crear un jugador (`POST`)

```http
POST /api/jugadores
Content-Type: application/json

{
  "nombre": "Lionel",
  "apellido": "Messi",
  "fecha_nacimiento": "1987-06-24",
  "posicion": "Delantero",
  "numero_camiseta": 10,
  "nacionalidad": "Argentina"
}
```

El recorrido es el mismo que en un `GET`, con un paso extra antes del controller: la **validación**.

```php
public function store(StoreJugadorRequest $request): JsonResponse
{
    $jugador = Jugador::create($request->validated());

    return (new JugadorResource($jugador))->response()->setStatusCode(201);
}
```

1. Laravel ve que el método recibe un `StoreJugadorRequest` (un **Form Request**: una clase dedicada a validar) y ejecuta sus reglas **antes** de entrar al método.
2. Si algún dato es inválido, corta y responde **422** con el detalle de cada error. El controller no llega a ejecutarse.
3. Si todo es válido, `$request->validated()` devuelve solo los campos validados y `Jugador::create()` hace el `INSERT`.
4. Responde **201 Created** con el jugador creado.

Reglas de `app/Http/Requests/StoreJugadorRequest.php`:

| Campo | Reglas |
|---|---|
| `nombre`, `apellido` | Obligatorio, texto, máximo 255 caracteres |
| `fecha_nacimiento` | Obligatorio, formato `AAAA-MM-DD`, anterior a hoy |
| `posicion` | Obligatorio, uno de: `Arquero`, `Defensor`, `Mediocampista`, `Delantero` |
| `numero_camiseta` | Obligatorio, entero entre 1 y 99, no repetido en la tabla |
| `nacionalidad` | Obligatorio, texto, máximo 100 caracteres |

Ejemplo de respuesta con errores (**422**):

```json
{
  "message": "El número de camiseta ya está asignado a otro jugador.",
  "errors": {
    "numero_camiseta": ["El número de camiseta ya está asignado a otro jugador."]
  }
}
```

---

## 7. Las clases que intervienen

### Resumen

| Clase / archivo | Rol |
|---|---|
| `routes/api.php` | Asocia cada endpoint con un método del controller |
| `JugadorController` | Recibe la request, pide los datos al modelo y devuelve la respuesta |
| `Jugador` | Modelo: representa la tabla `jugadores`; cada instancia es una fila |
| `JugadorResource` | Decide qué campos y con qué formato salen en el JSON |
| `StoreJugadorRequest` | Valida los datos recibidos al crear un jugador |
| `Controller` | Clase base de todos los controllers; acá solo tiene `#[OA\Info]` de Swagger |

En una frase: **el modelo habla con la base de datos y el resource decide cómo sale el JSON.**

### El modelo: `app/Models/Jugador.php`

```php
class Jugador extends Model
{
    use HasFactory;

    protected $table = 'jugadores';

    protected $fillable = ['nombre', 'apellido', 'fecha_nacimiento', 'posicion', 'numero_camiseta', 'nacionalidad'];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'numero_camiseta' => 'integer',
        ];
    }
}
```

Llama la atención que **no declara propiedades** como `nombre` o `apellido`. Eloquent lee las columnas de la fila y las expone como propiedades **en tiempo de ejecución**: si la tabla tiene la columna `nombre`, `$jugador->nombre` funciona sin declararla. Lo que hay en la clase es configuración:

| Línea | Qué hace |
|---|---|
| `extends Model` | Hereda todo el comportamiento de Eloquent: consultar, guardar, borrar |
| `use HasFactory;` | Agrega el método `Jugador::factory()`, que usa el seeder |
| `$table = 'jugadores'` | Nombre de la tabla. Laravel la deduce pasando la clase al plural en inglés, y le habría quedado `jugadors` |
| `$fillable` | Campos que se pueden asignar **en masa**, por ejemplo `Jugador::create($request->all())`. Si el cliente manda un campo que no está en la lista (como `id`), se ignora. Protege contra que alguien modifique campos que no debería |
| `casts()` | Convierte los valores que vienen de la base al tipo correcto: la fecha a un objeto **Carbon** (con métodos como `->age` o `->format('d/m/Y')`) y el número a `int` |

### El resource: `app/Http/Resources/JugadorResource.php`

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'nombre' => $this->nombre,
        'apellido' => $this->apellido,
        'fecha_nacimiento' => $this->fecha_nacimiento->toDateString(),
        'posicion' => $this->posicion,
        'numero_camiseta' => $this->numero_camiseta,
        'nacionalidad' => $this->nacionalidad,
    ];
}
```

El resource **envuelve al modelo** y le reenvía todo lo que le pidas: `$this->nombre` termina leyendo `$jugador->nombre`. `toArray()` define la forma exacta del JSON.

### Recorrido de un dato

**En la base:**

```
id=1 | nombre="Otto" | fecha_nacimiento="1990-06-13" | numero_camiseta="2" | created_at="2026-10-02 14:43:51" | ...
```

**En el modelo `Jugador`** (con los casts aplicados):

```
$jugador->id               → 1
$jugador->nombre           → "Otto"
$jugador->fecha_nacimiento → Carbon(1990-06-13 00:00:00)   ← objeto fecha
$jugador->numero_camiseta  → 2                             ← int
$jugador->created_at       → Carbon(...)                   ← está, pero es interno
```

**En la respuesta, después del `JugadorResource`:**

```json
{
  "data": {
    "id": 1,
    "nombre": "Otto",
    "fecha_nacimiento": "1990-06-13",
    "numero_camiseta": 2
  }
}
```

`created_at` y `updated_at` quedaron afuera y la fecha salió sin hora.

### ¿Por qué no devolver el modelo directamente?

`return $jugador;` también devuelve JSON, pero manda **todas** las columnas tal como están en la base. Si mañana se renombra una columna, cambia la respuesta para quien consume la API. **El modelo es la base de datos; el resource es el contrato de la API.**

---

## 8. Eloquent, el ORM de Laravel

Eloquent viene incluido en Laravel; no hace falta instalar nada.

Usa el patrón **Active Record**: el mismo objeto representa una fila y sabe consultarse, guardarse y borrarse. Las consultas se escriben como cadenas de métodos sobre el modelo:

| Eloquent | SQL que genera |
|---|---|
| `Jugador::all()` | `SELECT * FROM jugadores` |
| `Jugador::find(5)` | `SELECT * FROM jugadores WHERE id = 5` |
| `Jugador::findOrFail(5)` | ídem, pero responde 404 si no existe |
| `Jugador::where('posicion', 'Delantero')->get()` | `... WHERE posicion = 'Delantero'` |
| `Jugador::orderBy('numero_camiseta')->get()` | `... ORDER BY numero_camiseta` |
| `Jugador::where('nacionalidad', 'Argentina')->count()` | `SELECT COUNT(*) ... WHERE nacionalidad = 'Argentina'` |
| `Jugador::create([...])` | `INSERT INTO jugadores ...` |
| `$jugador->update([...])` | `UPDATE jugadores SET ... WHERE id = ?` |
| `$jugador->delete()` | `DELETE FROM jugadores WHERE id = ?` |

Algunas características:

- **`get()` ejecuta la consulta.** Hasta que no se llama `get()`, `first()`, `count()`, etc., solo se va armando el SQL.
- **Devuelve objetos.** `get()` devuelve una *Collection* de objetos `Jugador`; `find()` devuelve un `Jugador` o `null`.
- **Timestamps automáticos.** Al crear o modificar, Eloquent completa `created_at` y `updated_at` solo.
- **Seguridad.** Usa *prepared statements*, así que los valores quedan protegidos contra SQL injection.
- **Independiente del motor.** El motor se elige con `DB_CONNECTION` en el `.env` (`mysql`, `sqlite`, `pgsql`, `sqlsrv`) sin tocar código.

---

## 9. Swagger

Se usa el paquete **`darkaonline/l5-swagger`**, que genera la documentación en formato OpenAPI y la muestra con Swagger UI.

La documentación se escribe con **atributos PHP** `#[OA\...]` sobre el código:

- `#[OA\Info]` en `Controller.php`: título y versión de la API.
- `#[OA\Get]` sobre cada método del controller: ruta, parámetros y respuestas posibles.
- `#[OA\Schema]` en `JugadorResource.php`: la forma del objeto `Jugador`.

Estos atributos **no se ejecutan durante las requests**. Solo los lee este comando, que genera `storage/api-docs/api-docs.json`:

```bash
php artisan l5-swagger:generate
```

Hay que volver a correrlo cada vez que se modifican los atributos. La interfaz está en `/api/documentation`, y desde ahí se pueden probar los endpoints con el botón **Try it out**.

---

## 10. Comandos útiles

```bash
# Servidor
php artisan serve                      # levanta en http://localhost:8000
php artisan serve --port=8080          # en otro puerto

# Rutas
php artisan route:list                 # todas las rutas registradas
php artisan route:list --path=api      # solo las de la API

# Base de datos
php artisan migrate                    # corre migraciones pendientes
php artisan migrate:status             # estado de las migraciones
php artisan migrate:rollback           # deshace el último batch
php artisan migrate:fresh --seed       # ⚠️ borra todo y recrea con datos de prueba
php artisan db:seed                    # solo corre los seeders

# Generar clases
php artisan make:model Equipo -mf      # modelo + migración + factory
php artisan make:controller Api/EquipoController
php artisan make:resource EquipoResource
php artisan make:migration create_equipos_table
php artisan make:seeder EquipoSeeder

# Swagger
php artisan l5-swagger:generate        # regenera la documentación

# Consola interactiva (para probar consultas)
php artisan tinker
>>> App\Models\Jugador::count()
>>> App\Models\Jugador::where('posicion', 'Arquero')->get()

# Cachés (si algo de config o rutas "no se actualiza")
php artisan optimize:clear
```
