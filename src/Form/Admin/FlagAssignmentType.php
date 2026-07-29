<?php

namespace App\Form\Admin;

use App\Entity\Flag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FlagAssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $usernameAttributes = ['class' => 'member-autocomplete-start'];
        if ($options['username_readonly']) {
            $usernameAttributes['readonly'] = 'readonly';
        }

        $builder
            ->add('username', TextType::class, [
                'label' => 'AdminFlagsUserName',
                'attr' => $usernameAttributes,
                'constraints' => [new NotBlank(message: 'AdminFlagsUsernameEmpty')],
            ])
            ->add('flag', EntityType::class, [
                'class' => Flag::class,
                'choices' => $options['flags'],
                'choice_label' => 'name',
                'label' => 'AdminFlagsFlags',
                'placeholder' => '',
                'disabled' => $options['flag_disabled'],
                'constraints' => [new NotBlank(message: 'AdminFlagsNoFlagSelected')],
            ])
            ->add('level', ChoiceType::class, [
                'choices' => array_combine(range(1, 10), range(1, 10)),
                'label' => 'AdminFlagsLevel',
                'placeholder' => '',
                'constraints' => [new NotBlank(message: 'AdminFlagsNoLevelSelected')],
            ])
            ->add('scope', TextType::class, [
                'label' => 'AdminFlagsScope',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'AdminFlagsComment',
                'attr' => ['rows' => 4],
                'constraints' => [new NotBlank(message: 'AdminFlagsCommentEmpty')],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'AdminFlagsSubmit',
                'attr' => ['class' => 'btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'flags' => [],
            'username_readonly' => false,
            'flag_disabled' => false,
        ]);
        $resolver->setAllowedTypes('flags', 'array');
        $resolver->setAllowedTypes('username_readonly', 'bool');
        $resolver->setAllowedTypes('flag_disabled', 'bool');
    }
}
