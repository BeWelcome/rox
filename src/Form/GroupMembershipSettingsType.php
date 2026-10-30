<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

/**
 * A member's settings for a group they're in: why they're in it, and whether they get the group's mails and forum
 * notifications.
 */
class GroupMembershipSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'groupsmembercomments',
                'required' => false,
                'constraints' => [new Length(max: 2000)],
            ])
            ->add('acceptMassMail', CheckboxType::class, [
                'label' => 'groupsmemberacceptmail',
                'required' => false,
            ])
            ->add('notificationsEnabled', CheckboxType::class, [
                'label' => 'group.membership.notifications',
                'required' => false,
            ])
            ->add('shownOnProfile', CheckboxType::class, [
                'label' => 'group.profile.shown',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'membership_settings';
    }
}
