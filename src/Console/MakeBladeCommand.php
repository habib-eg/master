<?php


namespace Habib\Master\Console;


use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeBladeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:blade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Blade file';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Blade';

    /**
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath([
          'index'=>'/base_stubs/blade/index.stub',
          'create'=>'/base_stubs/blade/create.stub',
          'edit'=>'/base_stubs/blade/edit.stub',
          'show'=>'/base_stubs/blade/show.stub',
        ][$this->option('blade')]);
    }
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that the observer applies to.'],
            ['blade', 'b', InputOption::VALUE_OPTIONAL, 'The Blade that Selected.'],
        ];
    }

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
            : dirname(dirname(__DIR__)).$stub;
    }


    /**
     * Get full view path relative to the application's configured view path.
     *
     * @param string $path
     * @return string
     */
    protected function getViewPath($path)
    {
        return str_replace('\\','/',implode(DIRECTORY_SEPARATOR, [
            config('view.paths',[])[0] ?? resource_path('views'), $path,
        ])).".blade.php";
    }

    public function prefix($view)
    {
        return $this->option('model').'/'.$view;
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

        $name = $this->prefix($this->getNameInput());

        $path = $this->getViewPath($name);

        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') ||
                ! $this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $this->info("{$this->type} {$this->option('blade')} created successfully.");
    }

}
