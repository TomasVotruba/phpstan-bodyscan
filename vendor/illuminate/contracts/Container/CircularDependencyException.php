<?php

namespace PHPStanBodyscan202406\Illuminate\Contracts\Container;

use Exception;
use PHPStanBodyscan202406\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
