<?php
namespace Habib\Master\Console;

use Habib\Master\Providers\MasterServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallMasterConsole extends Command
{
//    protected $hidden = true;

    protected $signature = 'master:install';

    protected $description = 'Install the Master';
    protected $type="Master";
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

        $this->call('vendor:publish', [
            '--provider' => MasterServiceProvider::class,
//            '--tag' => "config"
        ]);
        $path =dirname(dirname(__DIR__)).'/stubs/base';

        $this->publishFiles(\File::files($c = $path.'/controller/base'),$c,app_path('Http/Controllers/Base'));
        $this->publishFiles(\File::files($m =$path.'/model/base'),$m,app_path('Models/Base'));
        $this->publishFiles(\File::files($m =$path.'/repository/base'),$m,app_path('Repository/Base'));
        $this->publishFiles(\File::files($m =$path.'/model'),$m,app_path('Models'));
        $this->publishFiles(\File::files($m =$path.'/traits'),$m,app_path('Traits'));
        $this->info('Installed Master');
    }

    public function publishFiles($files,$path,$folder)
    {
        foreach ($files as $file) {
            $fileName = $file->getRelativePathname();
            $name = str_replace('.stub','.php',ucfirst(\Str::camel($file->getRelativePathname())));
            $this->makeDirectory($folder."/{$name}");
            $this->publishFile($name,$folder,$path."/{$fileName}");
        }
    }

    /**
     * @return string
     */
    protected function getStub()
    {
        return  "";
    }
    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function publishFile($name,string $path,string $stub)
    {

        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') ||
                ! $this->option('force')) &&
            \File::exists($path.'/'.$name)) {
            $this->error($name.' already exists!');

            return false;
        }

        $this->makeDirectory($path);

        $this->compileStub($path."/{$name}",$stub);

        $this->info($name.' created successfully.');
    }


    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildMasterClass($name,$path)
    {
        $stub = $this->files->get($path);

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Compiles the "HomeController" stub.
     *
     * @return string
     */
    protected function compileStub($fileFullPath,$stubFullPath)
    {
        return file_put_contents($fileFullPath,str_replace(
            '{{namespace}}',
            $this->laravel->getNamespace(),
            file_get_contents($stubFullPath)
        ));
    }

    /**
     * Get full view path relative to the application's configured view path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getViewPath($path)
    {
        return implode(DIRECTORY_SEPARATOR, [
            config('view.paths')[0] ?? resource_path('views'), $path,
        ]);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

}
