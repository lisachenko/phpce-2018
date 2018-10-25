<?php

namespace App\Aspect;

use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

/**
 * Deferred aspect implementation
 */
class DeferredAspect implements Aspect
{
    private $delayedMethods = [];

    public function __construct()
    {
        register_shutdown_function([$this, 'onPhpTerminate']);
    }

    /**
     * Intercepts methods and delay their execution
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@execution(App\Annotation\Deferred)")
     * @return void
     */
    public function aroundDeferredMethods(MethodInvocation $invocation)
    {
        $this->delayedMethods[] = [
            $invocation->getMethod(),
            $invocation->getThis(),
            $invocation->getArguments()
        ];
        // do not call $invocation->proceed() right now, we call it later
        // $result = $invocation->proceed();

        // We can return instance of promise here for example
        return null;
    }

    public function onPhpTerminate()
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        };
        foreach ($this->delayedMethods as $delayedMethod) {
            /** @var $reflectionMethod \ReflectionMethod */
            list($reflectionMethod, $instance, $arguments) = $delayedMethod;
            $reflectionMethod->invokeArgs($instance, $arguments);
        }
    }
}
