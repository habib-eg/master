<?php

namespace Habib\Master\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class MakeRepositoryCommand extends GeneratorCommand
{
    protected $name = 'make:repository';

    protected $description = 'Make Repository';

    protected $type = "Repository";

    public function handle()
    {
//        parent::handle();
        $this->makeFile($repository = $this->getNameInput() . 'Repository');
        $this->type = "RepositoryInterface";
        $this->makeFile($repositoryInterface = $this->getNameInput() . 'RepositoryInterface', 'buildClassInterface');
        if (file_exists($path = config_path('master.php'))) {
            $configData = config('master', []);
            $namespace = 'App\Repository\\' . ucfirst($this->getNameInput()) . '\\';

            $configData['repositories'][$namespace . $repositoryInterface] = $namespace . $repository;
            $data = var_export($configData, 1);
            File::put($path, "<?php\n return $data ;");
            $this->info('Config updated successfully.');
        }
    }

    public function makeFile($input, $buildClass = "buildClass")
    {
        $name = $this->qualifyClass($input);

        $path = $this->getPath($name);

        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->alreadyExists($name)) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->{$buildClass}($name)));

        $this->info($this->type . ' created successfully.');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Repository\\' . ucfirst($this->getNameInput());
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath(dirname(dirname(__DIR__)) . '/stubs/base_stubs/repository.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : $stub;
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function buildClassInterface($name)
    {
        $stub = $this->files->get($this->getInterfaceStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    public function getInterfaceStub()
    {
        return $this->resolveStubPath(dirname(dirname(__DIR__)) . '/stubs/base_stubs/repository-interface.stub');
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $searches = [
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel','{{name}}'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}','{{ModelName}}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}','{{ name }}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel(),$this->getNameInput()],
                $stub,
            );
        }

        return $this;
    }

}
