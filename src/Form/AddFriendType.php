<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class AddFriendType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * Parameter $options not used but signature is given by symfony.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('confirm', CheckboxType::class, [
                'label' => 'profile.add.friendship.confirm',
                'required' => false,
            ])
            ->add('yes', ButtonType::class, [
                'label' => 'profile.add.friendship.yes',
            ])
            ->add('no', ButtonType::class, [
                'label' => 'profile.add.friendship.no',
            ])
        ;
    }
}
