<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\ModelMakeCommand as GeneratorCommand;
use Illuminate\Support\Str;

class ModelMakeCommand extends GeneratorCommand
{

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('pivot')
            ? $this->resolveStubPath(dirname(dirname(__DIR__)) . '/base_stubs/model.pivot.stub')
            : $this->resolveStubPath(dirname(dirname(__DIR__)) . '/base_stubs/model.stub');
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
            '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
        ]));

        $this->call(ControllerMakeCommand::class, array_filter([
            'name'  => "{$controller}/{$controller}ApiController",
            '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
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
            : $stub;
    }
}
