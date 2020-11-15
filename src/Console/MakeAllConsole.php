<?php

namespace Habib\Master\Console;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MakeAllConsole extends Command
{
    protected $signature = 'make:all {name}';

    protected $description = 'Install the Master';

    public function handle()
    {
        $name = $this->argument('name');

        $this->call(ModelMakeCommand::class, [
            "name" => $name,
            "-a" => true,
            "-p"=>$this->option('pivot')
        ]);

        $this->createRequest(ucfirst($name) . "/{$name}Request", ucfirst($name));

        $this->call('make:repository', ["name" => $name,]);

        $this->createObserver(ucfirst($name) . "/{$name}Observer", $name);
        $this->createPolicy(ucfirst($name) . "/{$name}Policy", $name);
        $this->createEventsAndListeners($name);
        $this->createResources($name);
        $this->createNotifications($name);
        $this->createTest($name);

        foreach (['index','create','edit','show']as $item) {
            $this->createBlade($item,$name,$item);
        }

    }

    public function createBlade($name,$model,$blade)
    {
        try {
            $this->call(MakeBladeCommand::class,[
                "name"=>$name,
                "--model"=>$model,
                "--blade"=>$blade
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
    public function createRequest($fullName, $model)
    {
        $this->createBase(RequestMakeCommand::class, $fullName, $model);
    }

    public function createBase($class, $fullName, $model)
    {
        try {
            $this->call($class, [
                "name" => $fullName,
                "--model" => $model,
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    public function createObserver($fullName, $model)
    {
        $this->createBase(ObserverMakeCommand::class, $fullName, $model);
    }

    public function createPolicy($fullName, $model)
    {
        $this->createBase(PolicyMakeCommand::class, $fullName, $model);
    }

    public function createEventsAndListeners($name)
    {
        $this->createEvent($createEvent = ucfirst($name) . "/{$name}CreateEvent", $name,);

        $this->createListener(ucfirst($name) . "/{$name}CreateListener",
            $this->laravel->getNamespace() . 'Events\\' . str_replace('/', '\\', $createEvent));

        $this->createEvent($updateEvent = ucfirst($name) . "/{$name}UpdateEvent", $name,);

        $this->createListener(ucfirst($name) . "/{$name}UpdateListener",
            $this->laravel->getNamespace() . 'Events\\' . str_replace('/', '\\', $updateEvent));

        $this->createEvent($deleteEvent = ucfirst($name) . "/{$name}DeleteEvent", $name,);

        $this->createListener(ucfirst($name) . "/{$name}DeleteListener",
            $this->laravel->getNamespace() . 'Events\\' . str_replace('/', '\\', $deleteEvent));

        $this->createEvent($deleteEvent = ucfirst($name) . "/{$name}RestoredEvent", $name,);

        $this->createListener(ucfirst($name) . "/{$name}RestoredListener",
            $this->laravel->getNamespace() . 'Events\\' . str_replace('/', '\\', $deleteEvent));

        $this->createEvent($deleteEvent = ucfirst($name) . "/{$name}ForceDeletedEvent", $name,);

        $this->createListener(ucfirst($name) . "/{$name}ForceDeletedListener",
            $this->laravel->getNamespace() . 'Events\\' . str_replace('/', '\\', $deleteEvent));

    }

    public function createEvent($fullName, $model)
    {
        $this->createBase(EventMakeCommand::class, $fullName, $model);
    }

    public function createListener($fullName, $event)
    {
        try {
            $this->call(ListenerMakeCommand::class, [
                "name" => $fullName,
                "-e" => $event,
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

    }

    public function createResources($name)
    {
        $upperCaseName = ucfirst($name);
        $lowerCaseName = strtolower($name);

        $this->createResource(
            $upperCaseName . "/{$name}Resource",
            $upperCaseName
        );

        $this->createResource(
            $upperCaseName . "/{$name}Collection",
            $upperCaseName
        );

    }

    public function createResource($fullName, $model)
    {

        try {
            $this->call(ResourceMakeCommand::class, [
                "name" => $fullName,
                "--model" => $model
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

    }

    public function createNotifications($name)
    {
        $upperCaseName = ucfirst($name);

        $lowerCaseName = strtolower($name);

        $this->createNotification($upperCaseName . "/{$name}CreateNotification", "mail." . strtolower($name) . ".create", $upperCaseName);

        $this->createNotification($upperCaseName . "/{$name}UpdateNotification", "mail." . $lowerCaseName . ".update", $upperCaseName);

        $this->createNotification($upperCaseName . "/{$name}DeleteNotification", "mail." . $lowerCaseName . ".delete", $upperCaseName);

        $this->createNotification($upperCaseName . "/{$name}RestoredNotification", "mail." . $lowerCaseName . ".restored", $upperCaseName);

        $this->createNotification($upperCaseName . "/{$name}ForceDeletedNotification", "mail." . $lowerCaseName . ".forceDeleted", $upperCaseName);
    }

    public function createNotification($fullName, $markdown, $model)
    {
        try {
            $this->call(NotificationMakeCommand::class, [
                "name" => $fullName,
                "--markdown" => $markdown,
                "--model" => $model
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    public function createTest($name)
    {
        $this->createBase(TestMakeCommand::class, ucfirst($name) . 'Test', ucfirst($name));

        $this->call(TestMakeCommand::class, [
            "name" => ucfirst($name) . 'UnitTest',
            "--model" => ucfirst($name),
            "-u" => true
        ]);
    }
    protected function getOptions()
    {
        return [
            ['pivot', 'p', InputOption::VALUE_NONE, 'Indicates if the generated model should be a custom intermediate table model'],

        ];
    }
}
