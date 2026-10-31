<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ForumNotificationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'label.username',
                'attr' => ['class' => 'member-autocomplete-start'],
                'constraints' => [new NotBlank()],
            ])
            ->add('enable', SubmitType::class, [
                'label' => 'admin.subscriptions.enable',
                'attr' => ['class' => 'btn-primary'],
            ])
            ->add('disable', SubmitType::class, [
                'label' => 'admin.subscriptions.disable',
                'attr' => ['class' => 'btn-outline-primary'],
            ])
        ;
    }
}
