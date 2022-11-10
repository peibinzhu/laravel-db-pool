<?php

declare(strict_types=1);

namespace PeibinLaravel\DbPool\Pool;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use PeibinLaravel\DbPool\Connection;
use PeibinLaravel\DbPool\Frequency;
use PeibinLaravel\Pool\Contracts\ConnectionInterface;
use PeibinLaravel\Pool\Pool;

class DbPool extends Pool
{
    protected array $config;

    public function __construct(Container $container, protected string $name)
    {
        $config = $container->get(Repository::class);
        $key = sprintf('database.connections.%s', $this->name);
        if (!$config->has($key)) {
            throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->config = $config->get($key);
        $options = Arr::get($this->config, 'pool', []);

        $this->frequency = $container->make(Frequency::class);
        parent::__construct($container, $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function createConnection(): ConnectionInterface
    {
        return new Connection($this->container, $this, $this->config, $this->name);
    }
}
