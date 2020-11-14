<?php
namespace Habib\Master\Console;

use Illuminate\Console\Command;

class MakeAllConsole extends Command
{
    protected $signature = 'make:all {name}';

    protected $description = 'Install the Master';
    public function handle()
    {
        $name =$this->argument('name');
        $this->call(ModelMakeCommand::class,[
            "name"=>$name,
            "-a"=>true
        ]);

        $this->call(EventMakeCommand::class,[
            "name"=>$createEvent =ucfirst($name)."/{$name}CreateEvent",
        ]);

        $this->call(ListenerMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}CreateListener",
            "-e"=>$this->laravel->getNamespace().'Events\\'.str_replace('/','\\',$createEvent)
        ]);

        $this->call(EventMakeCommand::class,[
            "name"=>$updateEvent =ucfirst($name)."/{$name}UpdateEvent",
        ]);

        $this->call(ListenerMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}UpdateListener",
            "-e"=>$this->laravel->getNamespace().'Events\\'.str_replace('/','\\',$updateEvent)
        ]);

        $this->call(EventMakeCommand::class,[
            "name"=>$deleteEvent =ucfirst($name)."/{$name}DeleteEvent",
        ]);

        $this->call(ListenerMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}DeleteListener",
            "-e"=>$this->laravel->getNamespace().'Events\\'.str_replace('/','\\',$deleteEvent)
        ]);

        $this->call(RequestMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}Request",
        ]);

        $this->call(ResourceMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}Resource",
        ]);

        $this->call(ResourceMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}Collection",
        ]);

        $this->call('make:repository',[
            "name"=>$name,
        ]);

        $this->call(ObserverMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}Observer",
        ]);

        $this->call(PolicyMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}Policy",
            "-m"=>$name
        ]);

        $this->call(NotificationMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}CreateNotification",
        ]);

        $this->call(NotificationMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}UpdateNotification",
        ]);

        $this->call(NotificationMakeCommand::class,[
            "name"=>ucfirst($name)."/{$name}DeleteNotification",
        ]);

    }
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

}
