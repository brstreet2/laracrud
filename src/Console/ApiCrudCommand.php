<?php

namespace Laracrud\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * Command to generate API CRUD functionality for a given model.
 */
class ApiCrudCommand extends Command
{
    protected $signature = 'brstreet:api-crud {model? : The name of the model (optional)}';

    protected $description = 'Generate API CRUD functionality for a given model';

    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    public function handle(): int
    {
        $modelName = $this->getModelName();
        $modelClass = $this->getModelClass($modelName);

        if (! class_exists($modelClass)) {
            $this->handleMissingModel($modelName);
        }

        $this->info('Generating API CRUD functionality');
        $this->generateCrud($modelName);

        $this->info("API CRUD for '$modelName' generated successfully!");

        return 0;
    }

    private function getModelName(): string
    {
        $modelName = $this->argument('model');

        if (! $modelName) {
            $this->info('No model name provided.');
            $modelName = $this->ask('Please enter the model name:');
        }

        return $this->normalizeModelName($modelName);
    }

    private function normalizeModelName(string $modelName): string
    {
        $modelName = Str::singular($modelName);

        return Str::studly($modelName);
    }

    private function getModelClass(string $modelName): string
    {
        return 'App\\Models\\'.Str::studly($modelName);
    }

    private function handleMissingModel(string $modelName): void
    {
        $this->warn("Model $modelName does not exist.");

        if ($this->confirm("Do you want to create the model '$modelName'?")) {
            $this->call('make:model', ['name' => "App\\Models\\$modelName"]);
            $this->info("Model $modelName created successfully.");

            if ($this->confirm("Do you want to create a migration file for '$modelName'?")) {
                $migrationName = 'create_'.Str::snake(Str::plural($modelName)).'_table';
                $this->call('make:migration', ['name' => $migrationName]);
                $this->info("Migration for $modelName created successfully.");
            }
        }
    }

    private function handleControllerAndResource(string $modelName): void
    {
        if ($this->confirm("Do you want to create a controller for '$modelName'?")) {
            $controllerName = Str::studly($modelName).'Controller';

            // Use the controller stub to generate the controller
            $controllerStub = $this->getControllerStub($modelName);
            $controllerPath = app_path('Http/Controllers/Api/'.$controllerName.'.php');
            $this->filesystem->put($controllerPath, $controllerStub);

            $this->info("Generated API Controller: $controllerPath");

            $resourceName = Str::studly($modelName).'Resource';
            $this->call('make:resource', ['name' => $resourceName]);
            $this->info("Generated API Resource: $resourceName");

            // Generate the request files
            $this->generateRequests($modelName);
        }
    }

    private function getControllerStub(string $modelName): string
    {
        $stub = file_get_contents(base_path('stubs/api-crud-controller.stub'));

        // Replace placeholders in the stub with dynamic content
        return str_replace(
            ['{{ModelName}}', '{{ModelNameVariable}}'],
            [Str::studly($modelName), Str::camel($modelName)],
            $stub
        );
    }

    private function generateRequests(string $modelName): void
    {
        $requestDirectory = app_path('Http/Requests/'.Str::studly($modelName));

        // Ensure the request directory exists
        if (! $this->filesystem->exists($requestDirectory)) {
            $this->filesystem->makeDirectory($requestDirectory, 0755, true);
        }

        // Generate IndexRequest
        $indexRequestClass = $requestDirectory.'/IndexRequest.php';
        $indexRequestContent = $this->getRequestStub('Index', $modelName);
        $this->filesystem->put($indexRequestClass, $indexRequestContent);
        $this->info("Generated IndexRequest: $indexRequestClass");

        // Generate CreateRequest
        $createRequestClass = $requestDirectory.'/CreateRequest.php';
        $createRequestContent = $this->getRequestStub('Create', $modelName);
        $this->filesystem->put($createRequestClass, $createRequestContent);
        $this->info("Generated CreateRequest: $createRequestClass");

        // Generate UpdateRequest
        $updateRequestClass = $requestDirectory.'/UpdateRequest.php';
        $updateRequestContent = $this->getRequestStub('Update', $modelName);
        $this->filesystem->put($updateRequestClass, $updateRequestContent);
        $this->info("Generated UpdateRequest: $updateRequestClass");
    }

    private function getRequestStub(string $type, string $modelName): string
    {
        $stub = file_get_contents(base_path('stubs/api-crud-request.stub'));

        // Replace placeholders in the stub with dynamic content
        return str_replace(
            ['{{ModelName}}', '{{Type}}'],
            [Str::studly($modelName), $type],
            $stub
        );
    }

    private function generateCrud(string $modelName): void
    {
        $this->handleControllerAndResource($modelName);
        $this->generateFactory($modelName);

        $migrationName = 'create_'.Str::snake(Str::plural($modelName)).'_table';
        $migrationPath = database_path('migrations');
        $existingMigrations = $this->filesystem->allFiles($migrationPath);
        $migrationExists = false;

        foreach ($existingMigrations as $existingMigration) {
            if (Str::contains($existingMigration->getFilename(), $migrationName)) {
                $migrationExists = true;
                break;
            }
        }

        if (! $migrationExists) {
            $this->call('make:migration', ['name' => $migrationName]);
            $this->info("Generated migration: $migrationName");
        } else {
            $this->warn("Migration already exists for '$modelName'.");
        }

        $this->addRoutes($modelName);
        $this->generateTest($modelName); // Add test generation
    }

    private function generateFactory(string $modelName): void
    {
        $factoryName = Str::studly($modelName).'Factory';
        if (! class_exists($factoryName)) {
            $this->call('make:factory', ['name' => $factoryName, '--model' => $modelName]);
            $this->info("Generated Factory: $factoryName");
        } else {
            $this->warn("Factory for $modelName already exists.");
        }
    }

    private function generateTest(string $modelName): void
    {
        $testName = Str::studly($modelName).'ControllerTest';
        $testFilePath = base_path('tests/Feature/'.$testName.'.php');

        if (! $this->filesystem->exists($testFilePath)) {
            $stub = file_get_contents(base_path('stubs/api-crud-test.stub'));

            // Use snake_case for route names to match ->names()
            $modelNameSnakeCase = Str::snake($modelName);
            $modelNamePluralSnakeCase = Str::plural($modelNameSnakeCase);

            $testContent = str_replace(
                ['{{ModelName}}', '{{ModelNameVariable}}', '{{ModelNamePlural}}', '{{ModelFactory}}', '{{model_plural_snake_case}}'],
                [
                    Str::studly($modelName),
                    Str::camel($modelName),
                    Str::plural(Str::camel($modelName)),
                    Str::studly($modelName).'Factory',
                    $modelNamePluralSnakeCase, // Use plural snake_case for route names
                ],
                $stub
            );

            $this->filesystem->put($testFilePath, $testContent);
            $this->info("Generated Test File: $testFilePath");
        } else {
            $this->warn("Test file for $modelName already exists.");
        }
    }

    private function addRoutes(string $modelName): void
    {
        $this->ensureApiRoutesFileExists();
        $routeFilePath = base_path('routes/api.php');
        $pluralKebabCase = Str::plural(Str::kebab($modelName));
        $pluralSnakeCase = Str::plural(Str::snake($modelName));
        $controllerClass = Str::studly($modelName).'Controller';

        $routeDefinition = <<<ROUTE

use App\\Http\\Controllers\\Api\\$controllerClass;

Route::apiResource('$pluralKebabCase', $controllerClass::class)->names('$pluralSnakeCase');

ROUTE;

        if ($this->filesystem->exists($routeFilePath)) {
            $existingRoutes = $this->filesystem->get($routeFilePath);
            if (str_contains($existingRoutes, "Route::apiResource('$pluralKebabCase',")) {
                $this->warn("Route for '$pluralKebabCase' already exists in api.php.");
            } else {
                $this->filesystem->append($routeFilePath, $routeDefinition);
                $this->info("Added route: Route::apiResource('$pluralKebabCase', $controllerClass::class)->names('$pluralSnakeCase');");
            }
        } else {
            $this->error('Could not locate api.php. Please ensure your routes directory exists.');
        }
    }

    private function ensureApiRoutesFileExists(): void
    {
        $routeFilePath = base_path('routes/api.php');

        if (! file_exists($routeFilePath)) {
            $this->call('install:api');
            $this->info('api.php route file created.');
        } else {
            $this->info('api.php already exists.');
        }
    }
}
