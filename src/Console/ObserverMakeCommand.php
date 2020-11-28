<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\ObserverMakeCommand as GeneratorCommand;

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

}
