<?php
namespace Habib\Master\Console;

use Illuminate\Routing\Console\ControllerMakeCommand as GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

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
            $stub = '/stubs/base_stubs/controller.nested.stub';
        } elseif ($this->option('model')) {
            $stub = '/stubs/base_stubs/controller.model.stub';
        } elseif ($this->option('invokable')) {
            $stub = '/stubs/base_stubs/controller.invokable.stub';
        } elseif ($this->option('resource')) {
            $stub = '/stubs/base_stubs/controller.stub';
        }

        if ($this->option('api') && is_null($stub)) {
            $stub = '/stubs/base_stubs/controller.api.stub';
        } elseif ($this->option('api') && ! is_null($stub) && ! $this->option('invokable')) {
            $stub = str_replace('.stub', '.api.stub', $stub);
        }

        $stub = $stub ?? '/stubs/base_stubs/controller.plain.stub';

        return $this->resolveStubPath($stub);
    }
    protected function getOptions()
    {
        return [
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller.'],
            ['prefix', null, InputOption::VALUE_NONE, 'prefix the controller.'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate a single method, invokable controller class.'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
            ['parent', 'p', InputOption::VALUE_OPTIONAL, 'Generate a nested resource controller class.'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller class.'],
        ];
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return \Illuminate\Console\GeneratorCommand
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $searches = [
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel','NamespacedDummyPrefix'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}','{{ namespacedPrefix }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}','{{namespacedPrefix}}'],
        ];
        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel(), $this->RequestClass()],
                $stub
            );
        }

        return $this;
    }

    /**
     * Get the model for the default guard's user provider.
     *
     * @return string|null
     */
    protected function RequestClass()
    {
        return($this->hasOption('prefix') && !empty($prefix = $this->option('prefix'))) ?  "\\$prefix" : null;
    }

}
