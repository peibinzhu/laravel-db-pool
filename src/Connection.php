<?php

declare(strict_types=1);

namespace PeibinLaravel\DbPool;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface as DbConnectionInterface;
use Illuminate\Database\Connectors\ConnectionFactory;
use PeibinLaravel\Contracts\StdoutLoggerInterface;
use PeibinLaravel\DbPool\Traits\DbConnection;
use PeibinLaravel\Pool\Connection as BaseConnection;
use PeibinLaravel\Pool\Contracts\ConnectionInterface;
use PeibinLaravel\Pool\Contracts\PoolInterface;
use PeibinLaravel\Pool\Exceptions\ConnectionException;
use Psr\Log\LoggerInterface;

class Connection extends BaseConnection implements ConnectionInterface, DbConnectionInterface
{
    use DbConnection;

    protected ?DbConnectionInterface $connection = null;

    protected ConnectionFactory $factory;

    protected LoggerInterface $logger;

    protected bool $transaction = false;

    public function __construct(
        Container $container,
        PoolInterface $pool,
        protected array $config,
        protected string $name
    ) {
        parent::__construct($container, $pool);

        $this->factory = $container->get(ConnectionFactory::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);

        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (!$this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    public function reconnect(): bool
    {
        $this->close();

        $this->connection = $this->factory->make($this->config);

        if ($this->connection instanceof \Illuminate\Database\Connection) {
            // Reset event dispatcher after db reconnect.
            if ($this->container->has(Dispatcher::class)) {
                $dispatcher = $this->container->get(Dispatcher::class);
                $this->connection->setEventDispatcher($dispatcher);
            }

            // Reset reconnector after db reconnect.
            $this->connection->setReconnector(function ($connection) {
                $this->logger->warning('Database connection refreshing.');
                if ($connection instanceof \Illuminate\Database\Connection) {
                    $this->refresh($connection);
                }
            });
        }

        $this->lastUseTime = microtime(true);
        return true;
    }

    public function close(): bool
    {
        if ($this->connection instanceof \Illuminate\Database\Connection) {
            $this->connection->disconnect();
        }

        unset($this->connection);

        return true;
    }

    public function release(): void
    {
        if ($this->connection instanceof \Illuminate\Database\Connection) {
            // Reset $recordsModified property of connection to false before the connection release into the pool.
            $this->connection->forgetRecordModificationState();
        }

        if ($this->isTransaction()) {
            $this->connection->rollBack(0);
            $this->logger->error('Maybe you\'ve forgotten to commit or rollback the MySQL transaction.');
        }

        parent::release();
    }

    public function setTransaction(bool $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function isTransaction(): bool
    {
        return $this->transaction;
    }

    /**
     * Refresh pdo and readPdo for current connection.
     */
    protected function refresh(\Illuminate\Database\Connection $connection)
    {
        $refresh = $this->factory->make($this->config);
        if ($refresh instanceof \Illuminate\Database\Connection) {
            $connection->disconnect();
            $connection->setPdo($refresh->getPdo());
            $connection->setReadPdo($refresh->getReadPdo());
        }

        $this->logger->warning('Database connection refreshed.');
    }
}
