<?php

namespace App\Providers;

use Ackintosh\Ganesha\Builder;
use Ackintosh\Ganesha\Storage\Adapter\Memcached as MemcachedAdapter;
use App\Aspect\CachingAspect;
use App\Aspect\CircuitBreakerAspect;
use App\Aspect\LoggingAspect;
use Illuminate\Cache\Repository as CacheContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AopServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LoggingAspect::class, function (Application $app) {
            return new LoggingAspect($app->make(LoggerInterface::class));
        });
        $this->app->singleton(CachingAspect::class, function (Application $app) {
            return new CachingAspect($app->make(CacheContract::class));
        });

        $this->app->singleton(CircuitBreakerAspect::class, function (Application $app) {
            $memcached = new \Memcached();
            $memcached->addServer('localhost', 11211);

            $circuitBreaker = Builder::build([
                'failureRateThreshold' => 5,
                'intervalToHalfOpen'   => 5,
                'minimumRequests'      => 2,
                'timeWindow'           => 30,
                'adapter'              => new MemcachedAdapter($memcached),
            ]);

            return new CircuitBreakerAspect($circuitBreaker);
        });

        $this->app->tag(
            [
                LoggingAspect::class,
                CachingAspect::class,
                CircuitBreakerAspect::class
            ],
            ['goaop.aspect']
        );
    }
}
