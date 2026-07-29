<?php

namespace App\Form\Admin;

use App\Entity\Right;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RightAssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $usernameAttributes = ['class' => 'member-autocomplete-start'];
        if ($options['username_readonly']) {
            $usernameAttributes['readonly'] = 'readonly';
        }

        $builder
            ->add('username', TextType::class, [
                'label' => 'AdminRightsUserName',
                'attr' => $usernameAttributes,
                'constraints' => [new NotBlank(message: 'AdminRightsUsernameEmpty')],
            ])
            ->add('right', EntityType::class, [
                'class' => Right::class,
                'choices' => $options['rights'],
                'choice_label' => 'name',
                'label' => 'AdminRightsRights',
                'placeholder' => '',
                'disabled' => $options['right_disabled'],
                'constraints' => [new NotBlank(message: 'AdminRightsNoRightSelected')],
            ])
            ->add('level', ChoiceType::class, [
                'choices' => array_combine(range(1, 10), range(1, 10)),
                'label' => 'AdminRightsLevel',
                'placeholder' => '',
                'constraints' => [new NotBlank(message: 'AdminRightsNoLevelSelected')],
            ])
            ->add('scope', TextType::class, [
                'label' => 'AdminRightsScope',
                'help' => 'admin.assignments.scope.help',
                'constraints' => [new NotBlank(message: 'AdminRightsScopeEmpty')],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'AdminRightsComment',
                'attr' => ['rows' => 4],
                'constraints' => [new NotBlank(message: 'AdminRightsCommentEmpty')],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'AdminRightsSubmit',
                'attr' => ['class' => 'btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'rights' => [],
            'username_readonly' => false,
            'right_disabled' => false,
        ]);
        $resolver->setAllowedTypes('rights', 'array');
        $resolver->setAllowedTypes('username_readonly', 'bool');
        $resolver->setAllowedTypes('right_disabled', 'bool');
    }
}
