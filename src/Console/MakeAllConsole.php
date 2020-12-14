<?php

namespace Habib\Master\Console;

use Closure;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class MakeAllConsole
 * @package Habib\Master\Console
 * @property-read  Command
 * @see Command
 */
class MakeAllConsole extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:all {name} {--p|pivot} {--f|prefix=}';

    /**
     * @var string
     */
    protected $description = 'Install the Master';
    /**
     * @var Filesystem
     */
    protected $files;
    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * @var string[]
     */
    protected $events = ['retrieved', 'creating', 'created', 'updating', 'updated', 'saving', 'saved', 'deleting', 'deleted', 'restoring', 'restored', 'replicating', 'forceDeleted',];

    protected $views = ['index', 'create', 'edit', 'show'];

    protected $tests = ['Create', 'Validation', 'Show', 'All', 'Update', 'Delete'];

    /**
     * MakeAllConsole constructor.
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $prefix = $this->getPrefixUppercase();
        $uppercaseName = ucfirst($name);
        $this->call(ModelMakeCommand::class, array_merge([
            "name" => $name,
        ], array_merge([
            "--prefix"=>$this->getPrefixUppercase()
        ],$this->option('pivot') ? [
            "-p" => $this->hasOption('pivot'),
        ] : [])));

        $this->createEventsAndListeners($name);
        $this->createFactory();
        $this->createMigration();
        $this->createSeeder();
        $this->createController();
        $this->createRequest("$prefix/$uppercaseName/{$name}Request", $name);
        $this->createObserver("$prefix/$uppercaseName/{$name}Observer", $name);
        $this->createPolicy("$prefix/$uppercaseName/{$name}Policy", $name);
        $this->createResources($name);
        $this->createNotifications($name);
        $this->createTest($uppercaseName);

        foreach ($this->getViews() as $item) {
            $this->createBlade($item, $name, $item);
        }

        $this->call('make:repository', ["name" => "$name","--prefix"=>$prefix]);

        $this->editConfig('master', function (&$configData) {
            $configData['listeners'] = array_merge($configData['listeners'] ?? [], $this->listeners);
            $namespace = $this->laravel->getNamespace() . 'Repository\\' . ucfirst($this->argument('name')) . '\\';
            $configData['repositories'][$namespace . ucfirst($this->argument('name')) . 'RepositoryInterface'] = $namespace . ucfirst($this->argument('name')) . 'Repository';
            return $configData;
        });
    }

    public function getPrefixUppercase()
    {
        if ($this->hasOption('prefix')) {
            return ucfirst($this->getPrefix());
        }
        return null;
    }

    /**
     * @param $name
     */
    public function createEventsAndListeners($name)
    {
        $prefix = $this->getPrefixUppercase();
        $prefixNamespace = empty($prefix) ? null : "\\$prefix";
        $upperCaseName = ucfirst($name);
        $lowerCaseName = strtolower($name);
        foreach ($this->getEvents() as $item) {
            $item = ucfirst($item);
            $this->createEvent("$prefix/" . $event = ucfirst($name) . "/{$name}{$item}Event", $name);
            $this->createListener(
                $listener = "$prefix/$upperCaseName/{$name}{$item}Listener",
                $event = $this->laravel->getNamespace() . "Events$prefixNamespace\\" . str_replace('/', '\\', $event)
            );
            $this->listeners[$this->laravel->getNamespace() . "Listeners$prefixNamespace\\" . str_replace('/', '\\', $listener)][] = $event;
        }
    }

    public function getEvents()
    {
        return array_merge($this->events, config('master.eventNames', []));
    }

    /**
     * @param $fullName
     * @param $model
     */
    public function createEvent($fullName, $model)
    {
        $this->createBase(EventMakeCommand::class, $fullName, $model);
    }

    /**
     * @param $class
     * @param $fullName
     * @param $model
     */
    public function createBase($class, $fullName, $model)
    {
        try {
            $this->call($class, [
                "name" => $fullName,
                "--model" => $model,
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * @param $fullName
     * @param $event
     */
    public function createListener($fullName, $event)
    {
        try {
            $this->call(ListenerMakeCommand::class, [
                "name" => $fullName,
                "-e" => $event,
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

    }

    /**
     * Create a model factory for the model.
     *
     * @return void
     */
    protected function createFactory()
    {
        $factory = Str::studly($this->argument('name'));

        $this->call(FactoryMakeCommand::class, [
            'name' => "{$factory}Factory",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name
        );
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return is_dir(app_path('Models')) ? $rootNamespace . '\\Models' : $rootNamespace;

    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        if ($this->option('pivot')) {
            $table = Str::singular($table);
        }
        try {

            $this->call(MigrateMakeCommand::class, [
                'name' => "create_{$table}_table",
                '--create' => $table,
            ]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Create a seeder file for the model.
     *
     * @return void
     */
    protected function createSeeder()
    {
        $seeder = Str::studly(class_basename($this->argument('name')));

        $this->call(SeederMakeCommand::class, [
            'name' => "{$seeder}Seeder",
        ]);
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $prefix = $this->getPrefixUppercase();

        $this->call(ControllerMakeCommand::class, array_filter([
            'name' => "$prefix/{$controller}/{$controller}Controller",
            '--model' => $modelName,
            '--prefix' => $prefix,
        ]));

        $this->call(ControllerMakeCommand::class, array_filter([
            'name' => str_replace('//','/',"/Api/$prefix/{$controller}/{$controller}Controller"),
            '--model' => $modelName,
            '--api' => true,
            '--prefix' => $prefix,
        ]));

    }

    /**
     * @param $fullName
     * @param $model
     */
    public function createRequest($fullName, $model)
    {
        $this->createBase(RequestMakeCommand::class, $fullName, $model);
    }

    /**
     * @param $fullName
     * @param $model
     */
    public function createObserver($fullName, $model)
    {
        $this->createBase(ObserverMakeCommand::class, $fullName, $model);
    }

    /**
     * @param $fullName
     * @param $model
     */
    public function createPolicy($fullName, $model)
    {
        $this->createBase(PolicyMakeCommand::class, $fullName, $model);
    }

    /**
     * @param $name
     */
    public function createResources($name)
    {
        $upperCaseName = ucfirst($name);
        $lowerCaseName = strtolower($name);
        $prefix = $this->getPrefixUppercase();

        $this->createResource(
            "$prefix/$upperCaseName/{$name}Resource",
            $upperCaseName
        );

        $this->createResource(
            "$prefix/$upperCaseName/{$name}Collection",
            $upperCaseName
        );

    }

    /**
     * @param $fullName
     * @param $model
     */
    public function createResource($fullName, $model)
    {

        try {
            $this->call(ResourceMakeCommand::class, [
                "name" => $fullName,
                "--model" => $model
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

    }

    /**
     * @param $name
     */
    public function createNotifications($name)
    {
        $upperCaseName = ucfirst($name);
        $lowerCaseName = strtolower($name);
        $prefix = $this->getPrefixUppercase();
        foreach (['retrieved', 'creating', 'created', 'updating', 'updated', 'saving', 'saved', 'deleting', 'deleted', 'restoring', 'restored', 'replicating', 'forceDeleted',] as $item) {
            $upper = ucfirst($item);
            $this->createNotification("$prefix/$upperCaseName/{$name}{$upper}Notification", "mail." . strtolower($name) . ".{$item}", $upperCaseName);
        }
    }

    /**
     * @param $fullName
     * @param $markdown
     * @param $model
     */
    public function createNotification($fullName, $markdown, $model)
    {
        try {
            $this->call(NotificationMakeCommand::class, [
                "name" => $fullName,
                "--markdown" => $markdown,
                "--model" => $model
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * @param $name
     */
    public function createTest($name)
    {
        $upperCaseName = ucfirst($name);
        $lowerCaseName = strtolower($name);
        $prefix = $this->getPrefixUppercase();
        foreach ($this->getTests() as $item) {
            $itemUppercase = ucfirst($item);
            $this->createBase(TestMakeCommand::class, "$prefix/$upperCaseName/$upperCaseName${itemUppercase}" . 'Test', ucfirst($name));
            $this->call(TestMakeCommand::class, [
                "name" => "$prefix/$upperCaseName/${upperCaseName}${itemUppercase}" . 'UnitTest',
                "--model" => ucfirst($name),
                "-u" => true,
            ]);
        }
    }

    public function getTests()
    {
        return array_merge($this->tests, config('master.testNames', []));
    }

    public function getViews()
    {
        return array_merge($this->views, config('master.viewNames', []));
    }

    /**
     * @param $name
     * @param $model
     * @param $blade
     */
    public function createBlade($name, $model, $blade)
    {
        $upperCaseName = ucfirst($model);
        $lowerCaseName = strtolower($model);
        $prefix = $this->getPrefixLowercase();

        try {
            $this->call(MakeBladeCommand::class, [
                "name" => "$prefix/$lowerCaseName/$name",
                "--model" => $model,
                "--blade" => $blade
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getPrefixLowercase()
    {
        if ($this->hasOption('prefix')) {
            return strtolower($this->getPrefix());
        }
        return null;
    }

    /**
     * @return array|bool|string|null
     */
    public function getPrefix()
    {
        if ($this->hasOption('prefix')) {
            return $this->option('prefix');
        }
        return null;
    }

    /**
     * @param string $configName
     * @param Closure $callback
     */
    public function editConfig(string $configName, Closure $callback)
    {
        $path = config_path($configName . '.php');
        $config = config($configName);
        $data = var_export($callback($config), 1) ?? [];
        $this->files->put($path, "<?php\n return $data ;");
        $this->info("Config {$configName} updated successfully.");
    }

    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : dirname(dirname(__DIR__)) . "/$stub";
    }

    /**
     * Qualify the given model class base name.
     *
     * @param string $model
     * @return string
     */
    protected function qualifyModel(string $model)
    {
        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($model, $rootNamespace)) {
            return $model;
        }

        return is_dir(app_path('Models'))
            ? $rootNamespace . 'Models\\' . $model
            : $rootNamespace . $model;
    }


}
