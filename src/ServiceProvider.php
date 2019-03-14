<?php

namespace Sumeeti\TransactionalDispatch;

use Illuminate\Events\Dispatcher as BaseEventDispatcher;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/transactional-events.php',
            'transactional-events'
        );

        if (! $this->app['config']->get('transactional-events.enable')) {
            return;
        }

        $this->app->afterResolving('db', function ($connectionResolver) {
            $eventDispatcher = $this->app->make(BaseEventDispatcher::class);
            $this->app->extend('events', function () use ($connectionResolver, $eventDispatcher) {
                $dispatcher = new EventDispatcher($connectionResolver, $eventDispatcher);
                $dispatcher->setTransactionalEvents($this->app['config']->get('transactional-events.transactional'));
                $dispatcher->setExcludedEvents($this->app['config']->get('transactional-events.excluded'));
                return $dispatcher;
            });
        });
    }
}

