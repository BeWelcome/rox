<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangeUsernameFormType extends AbstractType
{
    /** The usernames members can choose (as the signup checks them), for the browser's check of the field. */
    public const string PATTERN_USERNAME = '[A-Za-z](?!.*[-_.][-_.])[A-Za-z0-9-._]{2,18}[A-Za-z0-9]';

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldUsername', TextType::class, [
                'label' => 'admin.tools.change_username.old',
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 20,
                    'pattern' => self::PATTERN_USERNAME,
                    'placeholder' => 'admin.tools.change_username.old.placeholder',
                ],
            ])
            ->add('newUsername', TextType::class, [
                'label' => 'admin.tools.change_username.new',
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 20,
                    'pattern' => self::PATTERN_USERNAME,
                    'placeholder' => 'admin.tools.change_username.new.placeholder',
                ],
            ]);
    }
}
