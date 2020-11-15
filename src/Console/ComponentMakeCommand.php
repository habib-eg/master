<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\ComponentMakeCommand as GeneratorCommand;

class ComponentMakeCommand extends GeneratorCommand
{
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return dirname(dirname(__DIR__)).'/base_stubs/view-component.stub';
    }

}
