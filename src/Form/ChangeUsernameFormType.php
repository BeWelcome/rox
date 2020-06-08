<?php

namespace App\Form;

use SignupModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangeUsernameFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('oldUsername', TextType::class, [
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 20,
                    'pattern' => SignupModel::PATTERN_USERNAME,
                    'placeholder' => 'old.username',
                ],
            ])
            ->add('newUsername', TextType::class, [
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 20,
                    'pattern' => SignupModel::PATTERN_USERNAME,
                    'placeholder' => 'new.username',
                ],
            ])
            ->add('change_username', SubmitType::class, [
                'label' => 'label.admin.tools.change.username',
            ]);
    }
}
