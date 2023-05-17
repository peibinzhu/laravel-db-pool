<?php

declare(strict_types=1);

namespace PeibinLaravel\DbPool;

use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager as IlluminateDatabaseManager;
use PeibinLaravel\Coroutine\Coroutine;

class DatabaseManager extends IlluminateDatabaseManager
{
    /**
     * The Database server configurations.
     *
     * @var array
     */
    protected $config;

    public function __construct($app, ConnectionFactory $factory, array $config)
    {
        parent::__construct($app, $factory);

        $this->config = $config;
    }

    /**
     * Get a database connection instance.
     *
     * @param string|null $name
     * @return \Illuminate\Database\Connection
     */
    public function connection($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();

        if (!$this->isPoolConnection($name)) {
            return parent::connection($name);
        }

        return $this->app->get(ConnectionResolver::class)->connection($name);
    }

    /**
     * Disconnect from the given database.
     *
     * @param string|null $name
     * @return void
     */
    public function disconnect($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();

        if (!$this->isPoolConnection($name)) {
            parent::disconnect($name);
            return;
        }

        $this->app->get(ConnectionResolver::class)->connection($name)->disconnect();
    }

    /**
     * Reconnect to the given database.
     *
     * @param string|null $name
     * @return \Illuminate\Database\Connection
     */
    public function reconnect($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();

        if (!$this->isPoolConnection($name)) {
            return parent::reconnect($name);
        }

        return $this->app->get(ConnectionResolver::class)->connection($name)->reconnect();
    }

    /**
     * Determine if the connection is a pool connection.
     *
     * @param string $name
     * @return bool
     */
    protected function isPoolConnection(string $name): bool
    {
        return Coroutine::id() > 0 && isset($this->config[$name]['pool']);
    }
}
