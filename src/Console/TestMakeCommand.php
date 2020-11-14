<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\TestMakeCommand as GeneratorCommand;

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
            ? $this->resolveStubPath('/stubs/test.unit.stub')
            : $this->resolveStubPath('/stubs/test.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return
            file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : dirname(dirname(__DIR__)).'/'.trim($stub,'/');
    }

}
