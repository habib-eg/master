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
            : __DIR__.$stub;
    }
}
