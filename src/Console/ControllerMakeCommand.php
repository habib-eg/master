<?php
namespace Habib\Master\Console;

use Illuminate\Routing\Console\ControllerMakeCommand as GeneratorCommand;

class ControllerMakeCommand  extends GeneratorCommand
{

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
            : dirname(dirname(__DIR__)).$stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = null;

        if ($this->option('parent')) {
            $stub = '/base_stubs/controller.nested.stub';
        } elseif ($this->option('model')) {
            $stub = '/base_stubs/controller.model.stub';
        } elseif ($this->option('invokable')) {
            $stub = '/base_stubs/controller.invokable.stub';
        } elseif ($this->option('resource')) {
            $stub = '/base_stubs/controller.stub';
        }

        if ($this->option('api') && is_null($stub)) {
            $stub = '/base_stubs/controller.api.stub';
        } elseif ($this->option('api') && ! is_null($stub) && ! $this->option('invokable')) {
            $stub = str_replace('.stub', '.api.stub', $stub);
        }

        $stub = $stub ?? '/base_stubs/controller.plain.stub';

        return $this->resolveStubPath($stub);
    }

}
