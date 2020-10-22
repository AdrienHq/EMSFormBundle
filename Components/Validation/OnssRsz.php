<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsOnssRsz;
use Symfony\Component\Validator\Constraint;

final class OnssRsz extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new IsOnssRsz($this->value);
    }

    public function getHtml5Attribute(): array
    {
        return [];
    }
}
