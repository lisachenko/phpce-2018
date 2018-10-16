<?php

namespace App\Aspect;

use App\Annotation\Loggable;
use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Before;
use Psr\Log\LoggerInterface;

/**
 * Application logging aspect
 *
 * ./app/Aspect/LoggingAspect.php
 */
class LoggingAspect implements Aspect
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Writes a log info before method execution
     *
     * @param MethodInvocation $invocation
     * @Before("@execution(App\Annotation\Loggable)")
     */
    public function beforeMethod(MethodInvocation $invocation)
    {
        $invocationMethod = $invocation->getMethod();
        $logAnnotation    = $invocationMethod->getAnnotation(Loggable::class);
        $methodArguments  = $invocation->getArguments();
        $methodParameters = array_slice(
            $invocationMethod->getParameters(),
            0,
            count($methodArguments)
        );

        $methodContext = [];
        foreach ($methodParameters as $index => $methodParameter) {
            $methodContext[$methodParameter->name] = $methodArguments[$index];
        }

        $this->logger->log(
            $logAnnotation->severity,
            $logAnnotation->template,
            $methodContext
        );
    }
}
