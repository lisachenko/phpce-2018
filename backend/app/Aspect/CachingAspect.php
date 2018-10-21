<?php

namespace App\Aspect;

use App\Annotation\Cacheable;
use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use Illuminate\Contracts\Cache\Repository as CacheContract;

/**
 * Caching aspect
 */
class CachingAspect implements Aspect
{
    /**
     * @var CacheContract
     */
    private $cache;

    public function __construct(CacheContract $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Intercepts methods and cache them
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@execution(App\Annotation\Cacheable)")
     * @return mixed
     */
    public function aroundCacheable(MethodInvocation $invocation)
    {
        $key = (string) $invocation;
        $key .= ':' . sha1(json_encode($invocation->getArguments()));
        if (!$this->cache->has($key)) {
            // We can use ttl value from annotation
            $cacheable = $invocation->getMethod()->getAnnotation(Cacheable::class);
            $ttl       = $cacheable->time;
            $result    = $invocation->proceed();
            $this->cache->put($key, $result, $ttl);
        }

        return $this->cache->get($key);
    }
}
