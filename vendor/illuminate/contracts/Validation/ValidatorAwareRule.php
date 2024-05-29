<?php

namespace PHPStanBodyscan202405\Illuminate\Contracts\Validation;

use PHPStanBodyscan202405\Illuminate\Validation\Validator;
interface ValidatorAwareRule
{
    /**
     * Set the current validator.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator(Validator $validator);
}
