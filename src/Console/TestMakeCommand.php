<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\TestMakeCommand as GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class TestMakeCommand extends GeneratorCommand
{
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('unit')
            ? $this->resolveStubPath('/base_stubs/test.unit.stub')
            : $this->resolveStubPath('/base_stubs/test.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return
            file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                ? $customPath
                : dirname(dirname(__DIR__)) . '/' . trim($stub, '/');
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
            ['unit', 'u', InputOption::VALUE_NONE, 'Create a unit test.'],
        ];
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return \Illuminate\Console\GeneratorCommand
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $searches = [
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel', 'RecourseModel', '{{ modelVariable }}', '{{ modelPluralVariable }}'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}', '{{ model }}', '{{ modelVariable }}', '{{ modelPluralVariable }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}', '{{model}}', '{{ modelVariable }}', '{{ modelPluralVariable }}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel(), $this->option('model'), strtolower($this->option('model')), strtolower(Str::plural($this->option('model')))],
                $stub
            );
        }

        return $this;
    }
}
