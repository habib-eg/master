<?php
namespace Habib\Master\Console;

use Illuminate\Foundation\Console\EventMakeCommand as GeneratorCommand;

class EventMakeCommand extends GeneratorCommand
{

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return dirname(dirname(__DIR__)).'/stubs/event.stub';
    }

}
