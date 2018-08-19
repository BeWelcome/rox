<?php

namespace AppBundle\Form;

use AppBundle\Controller\SignupController;
use SignupModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FindUserFormType extends AbstractType
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
            ->add('term', TextType::class, [
                'attr' => [
                    'minlength' => 4,
                    'placeholder' => 'Part of username',
                ]
            ])
            ->add('Search', SubmitType::class);
    }
}
