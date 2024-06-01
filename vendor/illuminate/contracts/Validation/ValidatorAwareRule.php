<?php

namespace PHPStanBodyscan202406\Illuminate\Contracts\Validation;

use PHPStanBodyscan202406\Illuminate\Validation\Validator;
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
