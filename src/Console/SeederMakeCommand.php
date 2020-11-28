<?php

namespace Habib\Master\Console;

use Illuminate\Database\Console\Seeds\SeederMakeCommand as GeneratorCommand;

class SeederMakeCommand extends GeneratorCommand
{

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/base_stubs/seeder.stub');
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
