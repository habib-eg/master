<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\ResourceMakeCommand as GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ResourceMakeCommand extends GeneratorCommand
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
        return $this->collection()
            ? $this->resolveStubPath('/stubs/base_stubs/resource-collection.stub')
            : $this->resolveStubPath('/stubs/base_stubs/resource.stub');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
            ['collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection'],
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
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel','RecourseModel','{{ modelVariable }}','{{ modelPluralVariable }}'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}','{{ model }}','{{ modelVariable }}','{{ modelPluralVariable }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}','{{model}}','{{ modelVariable }}','{{ modelPluralVariable }}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel(),$this->option('model'),strtolower($this->option('model')),strtolower(Str::plural($this->option('model')))],
                $stub
            );
        }

        return $this;
    }
}
