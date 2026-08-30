<?php
namespace SimpleCMS\Framework\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'simplecms:init')]
class SimpleCMSInitCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'simplecms:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化框架依赖包';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->publishMaatwebsiteExcel();
        $this->publishSpatieMedialibraryMigration();
        $this->publishSpatieMedialibraryConfig();

        $this->info('SimpleCMS dependencies have been initialized successfully.');

        return self::SUCCESS;
    }


    /**
     * publish maatwebsite\excel
     *
     * @return void
     */
    protected function publishMaatwebsiteExcel(): void
    {
        if (! class_exists(\Maatwebsite\Excel\ExcelServiceProvider::class)) {
            $this->warn('Maatwebsite Excel package is not installed, skipping publish.');
            return;
        }

        $this->call('vendor:publish', [
            '--provider' => "Maatwebsite\Excel\ExcelServiceProvider",
            '--tag' => "config",
        ]);
    }

    /**
     * publish spatie/laravel-medialibrary migration
     *
     * @return void
     */
    protected function publishSpatieMedialibraryMigration(): void
    {
        if (! class_exists(\Spatie\MediaLibrary\MediaLibraryServiceProvider::class)) {
            $this->warn('Spatie Media Library package is not installed, skipping migration publish.');
            return;
        }

        $this->call('vendor:publish', [
            '--provider' => "Spatie\MediaLibrary\MediaLibraryServiceProvider",
            '--tag' => "medialibrary-migrations",
        ]);
    }
    /**
     * publish spatie/laravel-medialibrary
     *
     * @return void
     */
    protected function publishSpatieMedialibraryConfig(): void
    {
        if (! class_exists(\Spatie\MediaLibrary\MediaLibraryServiceProvider::class)) {
            $this->warn('Spatie Media Library package is not installed, skipping config publish.');
            return;
        }

        $this->call('vendor:publish', [
            '--provider' => "Spatie\MediaLibrary\MediaLibraryServiceProvider",
            '--tag' => "medialibrary-config",
        ]);
    }

}
