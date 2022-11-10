<?php

declare(strict_types=1);

namespace PeibinLaravel\DbPool\Pool;

use Illuminate\Contracts\Container\Container;

class PoolFactory
{
    /**
     * @var array<string, DbPool>
     */
    protected array $pools = [];

    public function __construct(protected Container $container)
    {
    }

    public function getPool(string $name): DbPool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        if ($this->container instanceof Container) {
            $pool = $this->container->make(DbPool::class, ['name' => $name]);
        } else {
            $pool = new DbPool($this->container, $name);
        }
        return $this->pools[$name] = $pool;
    }
}
