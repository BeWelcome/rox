<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class FeedbackFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->setMethod('GET')
            ->add('types', ChoiceType::class, [
                'label' => 'label.feedback.types',
                'choices' => $options['data']['categories'],
                'choice_translation_domain' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'class' => 'select2',
                ],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
