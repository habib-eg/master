<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\NotificationMakeCommand as GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class NotificationMakeCommand extends GeneratorCommand
{

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('markdown')
            ? dirname(dirname(__DIR__)).'/base_stubs/markdown-notification.stub'
            : dirname(dirname(__DIR__)).'/base_stubs/notification.stub';
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
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel','{{model}}','{{ modelVariable }}'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}','{{model}}','{{ modelVariable }}'],
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
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the notification already exists'],
            ['model', 'mo', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
            ['markdown', 'm', InputOption::VALUE_OPTIONAL, 'Create a new Markdown template for the notification'],
        ];
    }
}
