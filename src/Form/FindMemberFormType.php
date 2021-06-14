<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FindMemberFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'label.username',
                'attr' => [
                    'minlength' => 4,
                    'placeholder' => 'placeholder.username.part',
                ],
                'constraints' => [
                    'pattern',
                ],
            ])
            ->add('search_member', SubmitType::class, [
                'label' => 'label.search.member',
            ]);
    }
}
