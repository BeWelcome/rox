<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class JoinGroupType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('reason', TextType::class, [
                'label' => 'groupsmembercomments',
                'required' => true,
            ])
            ->add('notifications', ChoiceType::class, [
                'choices' => [
                    'yes' => 'yes',
                    'no' => 'no',
                ],
                'label' => 'groupsmemberacceptmail',
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'placeholder' => false,
                'error_bubbling' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'error.select.notification',
                    ]),
                ],
            ])
            ->add('join', SubmitType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
