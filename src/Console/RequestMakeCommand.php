<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\RequestMakeCommand as GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class RequestMakeCommand extends GeneratorCommand
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
        return $this->resolveStubPath('/stubs/base_stubs/request.stub');
    }

    /**
     * Get the console command options.
     * @return array|array[]
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),[
            ['prefix', 'p', InputOption::VALUE_OPTIONAL, 'Prefix.'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
        ]);
    }

    public function getPrefix()
    {
        return $this->hasOption('prefix') ? "\\"."{$this->option('prefix')}" : null;
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
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel','EventModel','{{ modelVariable }}','{{ DummyPrefix }}'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}','{{ model }}','{{ modelVariable }}','{{ prefix }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}','{{model}}','{{modelVariable}}','{{prefix}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel(),$this->option('model'),strtolower($this->option('model')),$this->getPrefix()],
                $stub
            );
        }

        return $this;
    }

}
