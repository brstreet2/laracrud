# LaraCRUD

LaraCRUD is a Laravel package designed to streamline the creation of API CRUD (Create, Read, Update, Delete) operations. It automates the generation of models, controllers, migrations, factories, routes, and more, enabling developers to focus on building features instead of repetitive boilerplate code.

> **Inspiration**  
> This package was inspired by the efficient workflows and best practices advocated at my workplace. Special thanks to my engineering manager for the insightful guidance and the foundational idea behind LaraCRUD.

---

## Features

- Generates Eloquent models with optional migrations.
- Creates API controllers with full CRUD functionality.
- Sets up API routes automatically.
- Produces factories for model testing.
- Supports interactive prompts for missing components.

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

This command will create:

- `app/Models/Post.php`
- `app/Http/Controllers/Api/PostController.php`
- `database/migrations/xxxx_xx_xx_create_posts_table.php`
- `database/factories/PostFactory.php`
- `routes/api.php` entry for the resource route

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

---

**Note:** To ensure that your `README.md` file is displayed on [Packagist](https://packagist.org), make sure that:

1. The `README.md` file is located in the root directory of your repository.
2. The file is named exactly `README.md` (case-sensitive).
3. Your repository is public and accessible.
4. You have submitted your package to Packagist using the correct repository URL.

Once these conditions are met, Packagist will automatically display the contents of your `README.md` file on your package's page.
