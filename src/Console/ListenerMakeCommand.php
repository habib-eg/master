<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\ListenerMakeCommand as GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

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
                ? dirname(dirname(__DIR__)).'/stubs/base_stubs/listener-queued.stub'
                : dirname(dirname(__DIR__)).'/stubs/base_stubs/listener-queued-duck.stub';
        }

        return $this->option('event')
            ? dirname(dirname(__DIR__)).'/stubs/base_stubs/listener.stub'
            : dirname(dirname(__DIR__)).'/stubs/base_stubs/listener-duck.stub';
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        // First we need to ensure that the given name is not a reserved word within the PHP
        // language and that the class name will actually be valid. If it is not valid we
        // can error now and prevent from polluting the filesystem using invalid files.
        if ($this->isReservedName($this->getNameInput())) {
            $this->error('The name "'.$this->getNameInput().'" is reserved by PHP.');

            return false;
        }

        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);
        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') ||
                ! $this->option('force')) &&
            $this->alreadyExists($name)) {
            $this->error($this->type.' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $this->info(" {$this->type} created successfully.");
    }
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),[
            ['prefix', 'p', InputOption::VALUE_OPTIONAL, 'Prefix.'],

        ]); // TODO: Change the autogenerated stub
    }
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return "$rootNamespace\Listeners{$this->getPrefix()}";
    }
    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return $this->files->exists($this->getPath($this->qualifyClass($rawName)));
    }
    public function getPrefix()
    {
        return ($this->hasOption('prefix') && !empty($this->hasOption('prefix'))&& !is_null($this->hasOption('prefix')) ) ? "\\{$this->option('prefix')}" : null;
    }
}
