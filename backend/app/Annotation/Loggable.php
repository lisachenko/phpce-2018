<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Psr\Log\LogLevel;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Loggable
{
    /**
     * Text template for the record
     *
     * @var string
     */
    public $template;

    /**
     * Default severity for the log record
     */
    public $severity = LogLevel::INFO;
}
