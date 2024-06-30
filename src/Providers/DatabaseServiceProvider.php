<?php

namespace AcornDB\Providers;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\QueueEntityResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Contracts\Queue\EntityResolver;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\ConnectionResolverInterface;
use AcornDB\Factory as EloquentFactory;
use Illuminate\Support\ServiceProvider;

/**
 * Database service provider
 *
 **/
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register primary Eloquent service and associated features
     *
     * @return void
     **/
    public function register(): void
    {
        Model::clearBootedModels();

        $this->app->bindIf(MigrationRepositoryInterface::class);

        $this->registerConnectionServices();

        $this->registerEloquentFactory();

        $this->registerQueueableEntityResolver();
    }

    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices(): void
    {
        $this->app->bindIf(ConnectionResolverInterface::class, 'db');

        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app, $app['db.factory']);
        });

        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });
    }

    /**
     * Register the Eloquent factory instance in the container.
     *
     * @return void
     */
    protected function registerEloquentFactory(): void
    {
        $this->app->singleton(Generator::class, function ($app) {
            return FakerFactory::create($app['config']->get('app.faker_locale', 'en_US'));
        });

        $this->app->singleton('db.eloquentFactory', function ($app) {
            return EloquentFactory::construct(
                $app->make(Generator::class),
                $this->app->databasePath('factories')
            );
        });
    }

    /**
     * Register the queueable entity resolver implementation.
     *
     * @return void
     */
    protected function registerQueueableEntityResolver(): void
    {
        $this->app->singleton(EntityResolver::class, function () {
            return new QueueEntityResolver();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     **/
    public function boot(): void
    {
        Model::setConnectionResolver($this->app['db']);
        Model::setEventDispatcher($this->app['events']);
    }
}
