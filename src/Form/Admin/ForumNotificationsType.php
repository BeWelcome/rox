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
            // a search with suggestions in the template (member/suggest)
            ->add('username', TextType::class, [
                'label' => 'label.username',
                'constraints' => [new NotBlank()],
            ])
            ->add('enable', SubmitType::class, [
                'label' => 'admin.subscriptions.enable',
            ])
            ->add('disable', SubmitType::class, [
                'label' => 'admin.subscriptions.disable',
            ])
        ;
    }
}
