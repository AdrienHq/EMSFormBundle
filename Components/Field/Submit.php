<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

final class Submit extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'submit';
    }

    public function getFieldClass(): string
    {
        return SubmitType::class;
    }

    public function getOptions(): array
    {
        return [
            'attr' => $this->getAttributes(),
            'label' => $this->config->getLabel(),
            'translation_domain' => false,
        ];
    }
}
