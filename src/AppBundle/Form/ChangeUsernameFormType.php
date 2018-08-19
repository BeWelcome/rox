<?php

namespace AppBundle\Form;

use AppBundle\Controller\SignupController;
use SignupModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangeUsernameFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $formBuilder
     * @param array                $options
     *
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
                    'placeholder' => 'Old Username',
                ]
            ])
            ->add('newUsername', TextType::class, [
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 20,
                    'pattern' => SignupModel::PATTERN_USERNAME,
                    'placeholder' => 'New Username',
                ],
            ])
            ->add('change', SubmitType::class);
    }
}
