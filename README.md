# LaraCRUD

LaraCRUD is a Laravel package designed to streamline the creation of API CRUD (Create, Read, Update, Delete) operations. It automates the generation of models, controllers, migrations, factories, routes, and more, enabling developers to focus on building features instead of repetitive boilerplate code.

> **Inspiration**  
> This package was inspired by the efficient workflows and best practices advocated at my workplace. Special thanks to my engineering manager for the insightful guidance and the foundational idea behind LaraCRUD.

---

## Features

- Generates Eloquent models with optional migrations.
- Creates API controllers with full CRUD functionality, including validation requests.
- Sets up API routes automatically with customizable naming conventions.
- Produces factories for model testing.
- Generates feature tests for the API CRUD operations.
- Supports interactive prompts for missing components.
- Leverages customizable stubs for controllers, requests, and tests.

---

## Installation

Install the package via Composer:

```bash
composer require brstreet2/laracrud --dev
```

If you prefer to install it globally:

```bash
composer global require brstreet2/laracrud
```

---

## Usage

To generate CRUD operations for a specific model:

```bash
php artisan brstreet:api-crud {ModelName}
```

If the model does not exist, the command will prompt you to create it along with an optional migration.

---

## Example

Generating CRUD for a `Post` model:

```bash
php artisan brstreet:api-crud Post
```

### Files and Routes Created

1. **Model**

   - File: `app/Models/Post.php`
   - If the model does not exist, the command will generate a new model.

2. **Controller**

   - File: `app/Http/Controllers/Api/PostController.php`
   - A full-featured controller is created under the `Api` namespace.

3. **Migration**

   - File: `database/migrations/xxxx_xx_xx_create_posts_table.php`
   - If no migration exists for the table, a new migration file is created.

4. **Factory**

   - File: `database/factories/PostFactory.php`
   - A factory is created to support testing and seeding the model.

5. **Routes**

   - Added in `routes/api.php`:
     ```php
     Route::apiResource('posts', PostController::class)->names('posts');
     ```
   - The route path uses plural kebab case (`posts`), and route names are in plural snake case (`posts.index`, `posts.store`, etc.).

6. **Request Classes** (generated if the controller is created)

   - Files:
     - `app/Http/Requests/Post/IndexRequest.php`
     - `app/Http/Requests/Post/CreateRequest.php`
     - `app/Http/Requests/Post/UpdateRequest.php`
   - These handle validation and permissions for API actions.

7. **Resource Class**

   - File: `app/Http/Resources/PostResource.php`
   - Encapsulates the model data for API responses.

8. **Feature Test**
   - File: `tests/Feature/PostControllerTest.php`
   - A test file is generated to validate the API's behavior.

---

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request.

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## Credits

- Developed by [brstreet2](https://github.com/brstreet2)
- Inspired by the engineering practices at my workplace, with special thanks to my engineering manager for the foundational idea.