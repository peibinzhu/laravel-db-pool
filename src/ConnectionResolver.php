<?php

declare(strict_types=1);

namespace PeibinLaravel\DbPool;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use PeibinLaravel\Context\Context;
use PeibinLaravel\Coroutine\Coroutine;
use PeibinLaravel\DbPool\Pool\PoolFactory;

class ConnectionResolver implements ConnectionResolverInterface
{
    protected Repository $config;

    protected PoolFactory $factory;

    public function __construct(protected Container $container)
    {
        $this->factory = $container->get(PoolFactory::class);
        $this->config = $this->container->get(Repository::class);
    }

    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $connection = null;
        $id = $this->getContextKey($name);
        if (Context::has($id)) {
            $connection = Context::get($id);
        }

        if (!$connection instanceof ConnectionInterface) {
            $pool = $this->factory->getPool($name);
            $connection = $pool->get();
            try {
                // PDO is initialized as an anonymous function, so there is no IO exception,
                // but if other exceptions are thrown, the connection will not return to the connection pool properly.
                $connection = $connection->getConnection();
                Context::set($id, $connection);
            } finally {
                if (Coroutine::id() > 0) {
                    Coroutine::defer(function () use ($connection, $id) {
                        Context::set($id, null);
                        $connection->release();
                    });
                }
            }
        }

        return $connection;
    }

    public function getDefaultConnection(): string
    {
        return $this->config->get('database.default');
    }

    public function setDefaultConnection($name)
    {
        $this->config->set('database.default', $name);
    }

    /**
     * The key to identify the connection object in coroutine context.
     * @param mixed $name
     */
    private function getContextKey(mixed $name): string
    {
        return sprintf('database.connection.%s', $name);
    }
}
