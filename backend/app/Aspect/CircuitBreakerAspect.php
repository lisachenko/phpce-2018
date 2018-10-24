<?php

namespace App\Aspect;

use Ackintosh\Ganesha;
use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

/**
 * Circuit breaker aspect
 */
class CircuitBreakerAspect implements Aspect
{
    /**
     * @var Ganesha
     */
    protected $circuitBreaker;

    public function __construct(Ganesha $circuitBreaker)
    {
        $this->circuitBreaker = $circuitBreaker;
    }

    /**
     * Performs protected method execution
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@execution(App\Annotation\Fuse)")
     *
     * @return mixed
     * @throws \Throwable
     */
    public function aroundProtectedCall(MethodInvocation $invocation)
    {
        $identifier = (string) $invocation;
        if (!$this->circuitBreaker->isAvailable($identifier)) {
            // Fail fast, can also throw an exception
            return [
                'errors' => [
                    'message'     => 'Temporary out of service',
                    'status_code' => 502
                ]
            ];
        }

        try {
            $result = $invocation->proceed();
            $this->circuitBreaker->success($identifier);
        } catch (\Throwable $e) {
            $this->circuitBreaker->failure($identifier);
            throw $e;
        }

        return $result;
    }
}
