<?php

namespace PHPStanBodyscan202501\Illuminate\Contracts\Container;

use Exception;
use PHPStanBodyscan202501\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
