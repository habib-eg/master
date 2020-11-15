<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\ListenerMakeCommand as GeneratorCommand;

class ListenerMakeCommand extends GeneratorCommand
{

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('queued')) {
            return $this->option('event')
                ? dirname(dirname(__DIR__)).'/base_stubs/listener-queued.stub'
                : dirname(dirname(__DIR__)).'/base_stubs/listener-queued-duck.stub';
        }

        return $this->option('event')
            ? dirname(dirname(__DIR__)).'/base_stubs/listener.stub'
            : dirname(dirname(__DIR__)).'/base_stubs/listener-duck.stub';
    }

}
