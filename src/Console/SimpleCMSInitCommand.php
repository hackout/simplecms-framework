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

    }


    /**
     * publish maatwebsite\excel
     *
     * @return void
     */
    protected function publishMaatwebsiteExcel()
    {

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
    protected function publishSpatieMedialibraryMigration()
    {

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
    protected function publishSpatieMedialibraryConfig()
    {

        $this->call('vendor:publish', [
            '--provider' => "Spatie\MediaLibrary\MediaLibraryServiceProvider",
            '--tag' => "medialibrary-config",
        ]);
    }

}
