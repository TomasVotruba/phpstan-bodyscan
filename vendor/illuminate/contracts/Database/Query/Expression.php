<?php

namespace PHPStanBodyscan202405\Illuminate\Contracts\Database\Query;

use PHPStanBodyscan202405\Illuminate\Database\Grammar;
interface Expression
{
    /**
     * Get the value of the expression.
     *
     * @param  \Illuminate\Database\Grammar  $grammar
     * @return string|int|float
     */
    public function getValue(Grammar $grammar);
}
