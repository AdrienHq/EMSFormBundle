<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components;

use EMS\FormBundle\Components\Field\AbstractForgivingNumberField;
use EMS\FormBundle\Components\Field\ChoiceSelectNested;
use EMS\FormBundle\Components\Field\FieldInterface;
use EMS\FormBundle\FormConfig\AbstractFormConfig;
use EMS\FormBundle\FormConfig\ElementInterface;
use EMS\FormBundle\FormConfig\FieldConfig;
use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\FormConfigFactory;
use EMS\FormBundle\FormConfig\MarkupConfig;
use EMS\FormBundle\FormConfig\SubFormConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Form extends AbstractType
{
    /** @var FormConfigFactory */
    private $configFactory;

    public function __construct(FormConfigFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->getConfig($options);

        foreach ($config->getElements() as $element) {
            if ($element instanceof FieldConfig) {
                $this->addField($builder, $element);
            } elseif ($element instanceof MarkupConfig || $element instanceof SubFormConfig) {
                $builder->add($element->getName(), $element->getClassName(), ['config' => $element]);
            }
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['form_config'] = $options['config'];

        parent::buildView($view, $form, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['ouuid', 'locale'])
            ->setDefault('config', null)
            ->setNormalizer('config', function (Options $options, $value) {
                return $value ? $value : $this->configFactory->create($options['ouuid'], $options['locale']);
            })
            ->setNormalizer('attr', function (Options $options, $value) {
                if (!isset($options['config'])) {
                    return $value;
                }

                /** @var FormConfig $config */
                $config = $options['config'];
                $value['id'] = $config->getId();
                $value['class'] = $config->getLocale();

                return $value;
            })
        ;
    }

    private function getConfig(array $options): AbstractFormConfig
    {
        if (isset($options['config'])) {
            return $options['config'];
        }

        throw new \Exception('Could not build form, config missing!');
    }

    protected function createField(FieldConfig $config): FieldInterface
    {
        $class = $config->getClassName();

        return new $class($config);
    }

    private function addField(FormBuilderInterface $builder, FieldConfig $element): void
    {
        $field = $this->createField($element);
        $configOption = ['field_config' => $element];
        $options = ChoiceSelectNested::class !== $element->getClassName() ? $field->getOptions() : \array_merge($field->getOptions(), $configOption);

        $builder->add($element->getName(), $field->getFieldClass(), $options);
        $this->addModelTransformers($builder, $element, $field);
    }

    private function addModelTransformers(FormBuilderInterface $builder, ElementInterface $element, FieldInterface $field): void
    {
        if ($field instanceof AbstractForgivingNumberField) {
            $builder->get($element->getName())
            ->addModelTransformer($field->getDataTransformer());
        }
    }
}
