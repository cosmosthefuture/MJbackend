<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateCustomControllerCommand extends Command
{
    protected $signature = 'custom:controller {name}';

    protected $description = 'Create a new controller with custom content';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name = $this->argument('name');
        $name = str_replace('Controller', '', $name);
        $controllerContent = file_get_contents(base_path('stubs/CustomController.stub')); // Load your controller stub

        // Replace placeholders in the stub with the controller name
        $controllerContent = str_replace('{{controllerName}}', ucfirst($name), $controllerContent);
        $controllerContent = str_replace('{{sm_controllerName}}', lcfirst($name), $controllerContent);

        $controllerPath = app_path("Http/Controllers/{$name}Controller.php");

        // Check if the controller file already exists
        if (file_exists($controllerPath)) {
            $this->error('Controller already exists!');
        } else {
            file_put_contents($controllerPath, $controllerContent);
            $this->info('Controller created successfully!');
        }
        Artisan::call("make:request {$name}/CreateRequest");
        Artisan::call("make:request {$name}/UpdateRequest");
        Artisan::call("make:request {$name}/ListingRequest");
    }
}