<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class FlagDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'AdminFlagsName',
                'constraints' => [new NotBlank(message: 'AdminFlagsNameEmpty')],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'AdminFlagsDescription',
                'attr' => ['rows' => 5],
                'constraints' => [new NotBlank(message: 'AdminFlagsDescriptionEmpty')],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'AdminFlagsCreate',
                'attr' => ['class' => 'btn-primary'],
            ])
        ;
    }
}
