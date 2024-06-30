<?php

namespace AcornDB\Providers;

use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\ShortcodeFacade;
use AcornDB\Console\Commands\Seeds\SeedCommand;
use AcornDB\Console\Commands\Seeds\SeederMakeCommand;
use AcornDB\Console\Commands\Factories\FactoryMakeCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Acorn database service provider
 *
 * @package AcornDB
 */
class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ShortcodeFacade::class, function () {
            return tap(new ShortcodeFacade(), function (ShortcodeFacade $facade) {
                $parser_class = $this->app['config']->get('database.shortcode_parser', RegularParser::class);
                $facade->setParser(new $parser_class());
            });
        });

        $this->commands([
            SeedCommand::class,
            SeederMakeCommand::class,
            FactoryMakeCommand::class,
        ]);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../publishes/config/database.php' => $this->app->configPath('database.php'),
        ], 'Acorn Database');

        $this->registerFrom($this->app->basePath('database/seeds'));
    }

    /**
     * Return the App directory.
     */
    protected function appDirectory(): string
    {
        return $this->app->basePath();
    }

    /**
     * Register database seeders
     *
     * @param  string $path
     * @return void
     **/
    protected function registerFrom(string $path): void
    {
        foreach (glob("$path/*.php") as $filename) {
            require $filename;
        }
    }
}
