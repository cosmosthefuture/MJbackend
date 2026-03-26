<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class CreateRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name}';

    protected $description = 'Create a new repository class';

    public function handle()
    {
        $name = $this->argument('name');
        $namespace = 'App\Http\Repositories';
        $className = ucfirst($name);
        $model = str_replace('Repository', '', $name);
        $model = str_replace('Repo', '', $model);
        $model = ucfirst($model);

        $content = "<?php\n\nnamespace $namespace;\n\nuse App\Http\Repositories\BaseRepo;\nuse App\Models\\$model;\n\nclass $className extends BaseRepo\n{\n    public function __construct($model \$model)\n    {\n        parent::__construct(\$model);\n    }\n\n    // Add your custom repository logic here\n}\n";

        $directory = app_path('Http/Repositories');
        $filePath = $directory . '/' . $className . '.php';

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($filePath)) {
            $this->error('Repository class already exists!');

            return;
        }

        File::put($filePath, $content);

        $this->info('Repository class created successfully!');
        Artisan::call("make:model {$model}");
    }
}