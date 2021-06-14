<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LogFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('types', ChoiceType::class, [
                'choices' => $options['data']['logTypes'],
                'choice_translation_domain' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'class' => 'select2',
                ],
            ])
            ->add('username', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'member-autocomplete',
                ],
            ]);
    }

    public function getBlockPrefix()
    {
        return 'log';
    }
}
