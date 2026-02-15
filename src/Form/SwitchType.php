<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwitchType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer($this);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['choices'] = $options['choices'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'compound' => false,
                'choices' => [],
                'default' => '',
                'error_bubbling' => false,
            ])
            ->addAllowedTypes('choices', 'array')
            ->addAllowedTypes('default', 'string')
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'switch';
    }

    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return '';
        }

        return $value;
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (!\is_string($value)) {
            return $value;
        }

        if ('' === $value) {
            return null;
        }

        return $value;
    }
}
