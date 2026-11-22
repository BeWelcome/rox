<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class FindUserFormType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('term', TextType::class, [
                'label' => 'admin.tools.find_member.term',
                'constraints' => [
                    new NotBlank(message: 'admin.tools.find_member.error.empty'),
                    new Length(min: 3, minMessage: 'admin.tools.find_member.error.short'),
                ],
                'attr' => [
                    'minlength' => 3,
                    'placeholder' => 'admin.tools.find_member.placeholder',
                ],
            ]);
    }
}
