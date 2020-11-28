<?php

namespace Habib\Master\Console;

use Exception;
use Illuminate\Console\Command;
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
    protected $signature = 'make:all {name} {--p|pivot}';

    /**
     * @var string
     */
    protected $description = 'Install the Master';

    /**
     *
     */
    public function handle()
    {
        $name = $this->argument('name');

        $this->call(ModelMakeCommand::class,array_merge( [
            "name" => $name,
        ],$this->option('pivot')?[
            "-p"=>$this->hasOption('pivot'),
        ]:[]));

        $this->createFactory();
        $this->createMigration();
        $this->createSeeder();
        $this->createController();
        $this->createRequest(ucfirst($name) . "/{$name}Request", ucfirst($name));
        $this->createObserver(ucfirst($name) . "/{$name}Observer", $name);
        $this->createPolicy(ucfirst($name) . "/{$name}Policy", $name);
        $this->createEventsAndListeners($name);
        $this->createResources($name);
        $this->createNotifications($name);
        $this->createTest(ucfirst($name).'Create');
        $this->createTest(ucfirst($name).'Validation');
        $this->createTest(ucfirst($name).'Show');
        $this->createTest(ucfirst($name).'All');
        $this->createTest(ucfirst($name).'Update');
        $this->createTest(ucfirst($name).'Delete');

        foreach (['index','create','edit','show']as $item) {
            $this->createBlade($item,$name,$item);
        }

        $this->call('make:repository', ["name" => $name,]);
    }

    /**
     * @param $name
     * @param $model
     * @param $blade
     */
    public function createBlade($name, $model, $blade)
    {
        try {
            $this->call(MakeBladeCommand::class,[
                "name"=>$name,
                "--model"=>$model,
                "--blade"=>$blade
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
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
    public function createEventsAndListeners($name)
    {
        foreach (['create','update','delete','restored','forceDeleted'] as $item) {
            $item=ucfirst($item);
            $this->createEvent($createEvent = ucfirst($name) . "/{$name}{$item}Event", $name,);

            $this->createListener(ucfirst($name) . "/{$name}{$item}Listener",
                $this->laravel->getNamespace() . 'Events\\' . str_replace('/', '\\', $createEvent));
        }
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
     * @param $name
     */
    public function createResources($name)
    {
        $upperCaseName = ucfirst($name);
        $lowerCaseName = strtolower($name);

        $this->createResource(
            $upperCaseName . "/{$name}Resource",
            $upperCaseName
        );

        $this->createResource(
            $upperCaseName . "/{$name}Collection",
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
        foreach ([ 'create', 'update', 'delete', 'restored', 'forceDeleted', ] as $item) {
            $upper=ucfirst($item);
            $this->createNotification($upperCaseName . "/{$name}{$upper}Notification", "mail." . strtolower($name) . ".{$item}", $upperCaseName);
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
        $this->createBase(TestMakeCommand::class, ucfirst($name) . 'Test', ucfirst($name));

        $this->call(TestMakeCommand::class, [
            "name" => ucfirst($name) . 'UnitTest',
            "--model" => ucfirst($name),
            "-u" => true
        ]);
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

            $this->call('make:migration', [
                'name' => "create_{$table}_table",
                '--create' => $table,
            ]);
        } catch (\Exception $e) {
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

        $this->call(ControllerMakeCommand::class, array_filter([
            'name'  => "{$controller}/{$controller}Controller",
            '--model' => $modelName ,
        ]));

        $this->call(ControllerMakeCommand::class, array_filter([
            'name'  => "Api/{$controller}/{$controller}Controller",
            '--model' => $modelName,
            '--api' => true,
        ]));

    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : dirname(dirname(__DIR__))."/$stub";
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
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
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    /**
     * Qualify the given model class base name.
     *
     * @param  string  $model
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
            ? $rootNamespace.'Models\\'.$model
            : $rootNamespace.$model;
    }
    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
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
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return is_dir(app_path('Models')) ? $rootNamespace.'\\Models' : $rootNamespace;

    }


}
