<?php

namespace App\Form;


use App\Entity\ProfileNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileNoteFilterType extends AbstractType
{
    public const ORDER_UPDATED = 1;
    public const ORDER_CATEGORY = 2;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = array_merge(['' => ''], $options['categories']);

        $builder
            ->add('categories', ChoiceType::class, [
                'label' => 'profile.note.categories',
                'required' => false,
                'placeholder' => 'profile.note.select.category',
                'choices' => $categories,
                'autocomplete' => true,
                'empty_data' => [],
            ])
            ->add('order', ChoiceType::class, [
                'label' => 'profile.note.order',
                'required' => true,
                'choices' => [
                    'profile.note.updated' => self::ORDER_UPDATED,
                    'profile.note.category' => self::ORDER_CATEGORY,
                ],
            ])
            ->setMethod('GET')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'categories' => [],
                'order' => 1,
            ])
        ;
    }
}
