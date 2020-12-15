<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\ObserverMakeCommand as GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ObserverMakeCommand extends GeneratorCommand
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
        return $this->option('model')
            ? $this->resolveStubPath('/stubs/base_stubs/observer.stub')
            : $this->resolveStubPath('/stubs/base_stubs/observer.plain.stub');
    }

    /**
     * Replace the model for the given stub.
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceModel($stub, $model)
    {
        $modelClass = $this->parseModel($model);

        $replace = [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            '{{prefix}}' => $this->getPrefix(),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ];

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }

    public function getPrefix()
    {
        return $this->hasOption('prefix') ? "\\"."{$this->option('prefix')}" : null;
    }
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return "$rootNamespace\Observers";
    }
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),[
            ['prefix', 'p', InputOption::VALUE_OPTIONAL, 'Prefix.'],
        ]);
    }
}
