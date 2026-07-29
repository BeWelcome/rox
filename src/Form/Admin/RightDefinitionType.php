<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class RightDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'AdminRightsName',
                'constraints' => [new NotBlank(message: 'AdminRightsNameEmpty')],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'AdminRightsDescription',
                'attr' => ['rows' => 5],
                'constraints' => [new NotBlank(message: 'AdminRightsDescriptionEmpty')],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'AdminRightsCreate',
                'attr' => ['class' => 'btn-primary'],
            ])
        ;
    }
}
