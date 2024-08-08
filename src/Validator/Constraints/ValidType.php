<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidType extends Constraint
{
    public $message = 'The type "{{ value }}" is not valid.';
}