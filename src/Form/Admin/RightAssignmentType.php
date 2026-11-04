<?php

namespace App\Form\Admin;

use App\Entity\Right;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Assign a right to a member, or edit an assignment (templates/admin/rights/form.html.twig). The member is fixed when
 * the form is opened for them or edits an assignment; the right when it edits one. An empty scope is All.
 */
class RightAssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'admin.rights.member',
                'disabled' => $options['username_locked'],
                'attr' => ['class' => 'member-autocomplete-start', 'placeholder' => 'admin.rights.username'],
                'constraints' => [new NotBlank(message: 'admin.rights.error.member')],
            ])
            ->add('right', EntityType::class, [
                'class' => Right::class,
                'choices' => $options['rights'],
                'choice_label' => 'name',
                // the hint under the form shows the chosen right's description (assets/js/admin/rights.js)
                'choice_attr' => static fn (Right $right): array => ['data-description' => (string) $right->getDescription()],
                'label' => 'admin.rights.right',
                'placeholder' => 'admin.rights.choose',
                'disabled' => $options['right_locked'],
                'constraints' => [new NotBlank(message: 'admin.rights.error.right')],
            ])
            ->add('level', IntegerType::class, [
                'label' => 'admin.rights.level',
                'attr' => ['min' => 1, 'max' => 10],
                'constraints' => [
                    new NotBlank(message: 'AdminRightsNoLevelSelected'),
                    new Range(min: 1, max: 10),
                ],
            ])
            ->add('scope', TextType::class, [
                'label' => 'admin.rights.scope',
                'required' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => 'All'],
                'help' => 'admin.assignments.scope.help',
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'admin.rights.comment',
                'attr' => ['rows' => 3],
                'constraints' => [new NotBlank(message: 'AdminRightsCommentEmpty')],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'rights' => [],
            'username_locked' => false,
            'right_locked' => false,
        ]);
        $resolver->setAllowedTypes('rights', 'array');
        $resolver->setAllowedTypes('username_locked', 'bool');
        $resolver->setAllowedTypes('right_locked', 'bool');
    }
}
