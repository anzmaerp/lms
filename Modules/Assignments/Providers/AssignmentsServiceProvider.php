<?php

namespace Modules\Assignments\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

use Livewire\Livewire;
use Modules\Assignments\Livewire\Pages\Tutor\CreateAssignment\CreateAssignment;
use Modules\Assignments\Livewire\Pages\Tutor\AssignmentsList\AssignmentsList;
use Modules\Assignments\Livewire\Pages\Student\SubmitAssignment\SubmitAssignment;
use Modules\Assignments\Livewire\Pages\Student\AsignmentList\AssignmentList;
use Modules\Assignments\Livewire\Pages\Student\AssignmentAttempt\AssignmentAttempt;
use Modules\Assignments\Livewire\Pages\Student\AssignmentResult\AssignmentResult;
use Modules\Assignments\Livewire\Pages\Tutor\AssignmentsList\SubmissionsAssignmentsList;
use Modules\Assignments\Livewire\Pages\Tutor\AssignmentMark\AssignmentMark;
use RecursiveDirectoryIterator;

use RecursiveIteratorIterator;

class AssignmentsServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Assignments';

    protected string $nameLower = 'assignments';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
        Livewire::component('assignments::create-assignment', CreateAssignment::class);
        Livewire::component('assignments::assignments-list',  AssignmentsList::class);
        Livewire::component('assignments::assignment-list',  AssignmentList::class);
        Livewire::component('assignments::submit-assignment',  SubmitAssignment::class);
        Livewire::component('assignments::assignment-attempt',  AssignmentAttempt::class);
        Livewire::component('assignments::assignment-result',  AssignmentResult::class);
        Livewire::component('assignments::submissions-assignments-list',  SubmissionsAssignmentsList::class);
        Livewire::component('assignments::assignment-mark',  AssignmentMark::class);
    
    
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'resources/lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'resources/lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $relativeConfigPath = config('modules.paths.generator.config.path');
        $configPath = module_path($this->name, $relativeConfigPath);

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = str_replace($configPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $configKey = $this->nameLower . '.' . str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $relativePath);
                    $key = ($relativePath === 'config.php') ? $this->nameLower : $configKey;

                    $this->publishes([$file->getPathname() => config_path($relativePath)], 'config');
                    $this->mergeConfigFrom($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        $componentNamespace = $this->module_namespace($this->name, $this->app_path(config('modules.paths.generator.component-class.path')));
        Blade::componentNamespace($componentNamespace, $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
