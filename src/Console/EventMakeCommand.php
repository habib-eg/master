<?php
namespace Habib\Master\Console;

use Illuminate\Foundation\Console\EventMakeCommand as GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class EventMakeCommand extends GeneratorCommand
{

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return dirname(dirname(__DIR__)) . '/base_stubs/event.stub';
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
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel','EventModel','{{ modelVariable }}'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}','{{ model }}','{{ modelVariable }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}','{{model}}','{{ modelVariable }}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel(),$this->option('model'),strtolower($this->option('model'))],
                $stub
            );
        }

        return $this;
    }

    /**
     * @return array|array[]
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
        ];
    }

}
