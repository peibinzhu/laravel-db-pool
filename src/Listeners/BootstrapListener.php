<?php

declare(strict_types=1);

namespace PeibinLaravel\DbPool\Listeners;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Eloquent\Model;
use PeibinLaravel\DbPool\ConnectionResolver;
use PeibinLaravel\DbPool\DatabaseManager;
use PeibinLaravel\DbPool\Pool\PoolFactory;
use PeibinLaravel\Utils\ApplicationContext;

class BootstrapListener
{
    private array $warmServices = [
        'db',
        'db.factory',
        'db.transactions',
        PoolFactory::class,
        ConnectionResolver::class,
    ];

    public function __construct(protected Container $container)
    {
    }

    public function handle(object $event): void
    {
        $this->container->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        $this->container->singleton('db', function ($app) {
            $config = $this->container->get('config')->get('database.connections', []);
            return new DatabaseManager($app, $app['db.factory'], $config);
        });

        $this->container->bind('db.schema', function ($app) {
            return $app['db']->connection()->getSchemaBuilder();
        });

        $this->container->singleton('db.transactions', function ($app) {
            return new DatabaseTransactionsManager();
        });

        $this->container->singleton(PoolFactory::class);
        $this->container->singleton(ConnectionResolver::class);

        Model::setConnectionResolver($this->container['db']);
        Model::setEventDispatcher($this->container['events']);

        // The bindings listed below will be preloaded, avoiding repeated instantiation.
        foreach ($this->warmServices as $service) {
            if (is_string($service) && $this->container->bound($service)) {
                $this->container->get($service);
            }
        }

        ApplicationContext::setContainer($this->container);
    }
}
