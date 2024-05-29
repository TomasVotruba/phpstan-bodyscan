<?php

namespace PHPStanBodyscan202405\Illuminate\Contracts\Container;

use Exception;
use PHPStanBodyscan202405\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
