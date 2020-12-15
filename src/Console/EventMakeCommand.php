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
        return dirname(dirname(__DIR__)) . '/stubs/base_stubs/event.stub';
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
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}','{{model}}','{{ modelVariable }}','{{prefix}}'],
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

    public function getPrefix()
    {
        return $this->hasOption('prefix') ? "\\"."{$this->option('prefix')}" : null;
    }
    /**
     * @return array|array[]
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),[
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
            ['prefix', 'p', InputOption::VALUE_OPTIONAL, 'Prefix.'],
        ]);
    }

}
