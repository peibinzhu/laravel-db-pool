<?php

declare(strict_types=1);

namespace PeibinLaravel\DbPool;

use Illuminate\Support\ServiceProvider;
use PeibinLaravel\DbPool\Listeners\BootApplicationListener;
use PeibinLaravel\DbPool\Pool\PoolFactory;
use PeibinLaravel\ProviderConfig\Contracts\ProviderConfigInterface;
use PeibinLaravel\SwooleEvent\Events\BootApplication;

class DbPoolServiceProvider extends ServiceProvider implements ProviderConfigInterface
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                PoolFactory::class        => PoolFactory::class,
                ConnectionResolver::class => ConnectionResolver::class,
            ],
            'listeners'    => [
                BootApplication::class => BootApplicationListener::class,
            ],
        ];
    }
}
