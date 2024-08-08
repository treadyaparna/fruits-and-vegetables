<?php

namespace App\Validator\Constraints;

use App\Enum\SuperMarketGlobals;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidTypeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!in_array($value, [SuperMarketGlobals::ITEM_TYPE_FRUIT->value, SuperMarketGlobals::ITEM_TYPE_VEGETABLE->value])) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}