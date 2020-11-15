<?php

namespace Habib\Master\Console;

use File;
use Habib\Master\Providers\MasterServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Str;

class InstallMasterConsole extends Command
{
//    protected $hidden = true;

    protected $signature = 'master:install';

    protected $description = 'Install the Master';
    protected $type = "Master";
    /**
     * @var Filesystem
     */
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $this->info('Installing Master...');

        $this->info('Publishing configuration...');

//        $this->call('vendor:publish', [
//            '--provider' => MasterServiceProvider::class,
////            '--tag' => "config"
//        ]);

        $this->publishFiles(File::files($c = $this->resolveStubPath('/controller/base')), $c, app_path('Http/Controllers/Base'));
        $this->publishFiles(File::files($m = $this->resolveStubPath('/model/base')), $m, app_path('Models/Base'));
        $this->publishFiles(File::files($m = $this->resolveStubPath('/repository/base')), $m, app_path('Repository/Base'));
        $this->publishFiles(File::files($m = $this->resolveStubPath('/model')), $m, app_path('Models'));
        $this->publishFiles(File::files($m = $this->resolveStubPath('/traits')), $m, app_path('Traits'));
        $this->info('Installed Master');
    }

    public function publishFiles($files, $path, $folder)
    {
        foreach ($files as $file) {
            $fileName = $file->getRelativePathname();
            $name = str_replace('.stub', '.php', ucfirst(Str::camel($file->getRelativePathname())));
            $this->makeDirectory($folder . "/{$name}");
            try {
                $this->publishFile( $folder."/{$name}", $path . "/{$fileName}",str_replace('.php','',$name));
            } catch (FileNotFoundException $e) {
                $this->error($e->getMessage());
            }
        }
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Execute the console command.
     *
     * @param $fileFullPath
     * @param string $stub
     * @param string $type
     * @return bool|null
     *
     */
    public function publishFile($fileFullPath, string $stub,string $type="file")
    {

        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            File::exists($fileFullPath)) {
            $this->error($type . ' already exists!');

            return false;
        }

        $this->makeDirectory($fileFullPath);

        $this->compileStub($fileFullPath, $stub);

        $this->info($type . ' created successfully.');
    }

    /**
     * Compiles the "HomeController" stub.
     *
     * @return string
     */
    protected function compileStub($fileFullPath, $stubFullPath)
    {
        return file_put_contents($fileFullPath, str_replace(
            '{{namespace}}',
            $this->laravel->getNamespace(),
            file_get_contents($stubFullPath)
        ));
    }

    /**
     * Get full view path relative to the application's configured view path.
     *
     * @param string $path
     * @return string
     */
    protected function getViewPath($path)
    {
        return implode(DIRECTORY_SEPARATOR, [
            config('view.paths',[])[0] ?? resource_path('views'), $path,
        ]);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @param $default
     * @return string
     */
    protected function resolveStubPath($stub,string $default="base_stubs/base")
    {
        $default =trim($default,'/');
        return
            file_exists($customPath = $this->laravel->basePath("/{$default}/".trim($stub, '/')))
                ? $customPath
                : dirname(dirname(__DIR__)) . "/{$default}/".trim($stub,'/');
    }
}
