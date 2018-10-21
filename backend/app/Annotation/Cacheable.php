<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Cacheable
{
    /**
     * Time to cache
     *
     * @var integer
     */
    public $time;
}
