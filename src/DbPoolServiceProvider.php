<?php

declare(strict_types=1);

namespace PeibinLaravel\DbPool;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use PeibinLaravel\DbPool\Listeners\BootApplicationListener;
use PeibinLaravel\DbPool\Pool\PoolFactory;
use PeibinLaravel\SwooleEvent\Events\BootApplication;

class DbPoolServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $dependencies = [
            PoolFactory::class        => PoolFactory::class,
            ConnectionResolver::class => ConnectionResolver::class,
        ];
        $this->registerDependencies($dependencies);

        $listeners = [
            BootApplication::class => BootApplicationListener::class,
        ];
        $this->registerlisteners($listeners);
    }

    private function registerDependencies(array $dependencies)
    {
        $config = $this->app->get(Repository::class);
        foreach ($dependencies as $abstract => $concrete) {
            $concreteStr = is_string($concrete) ? $concrete : gettype($concrete);
            if (is_string($concrete) && method_exists($concrete, '__invoke')) {
                $concrete = function () use ($concrete) {
                    return $this->app->call($concrete . '@__invoke');
                };
            }
            $this->app->singleton($abstract, $concrete);
            $config->set(sprintf('dependencies.%s', $abstract), $concreteStr);
        }
    }

    private function registerListeners(array $listeners)
    {
        $dispatcher = $this->app->get(Dispatcher::class);
        foreach ($listeners as $event => $_listeners) {
            foreach ((array)$_listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }
}
