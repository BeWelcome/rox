<?php

namespace App\Form;


use App\Entity\ProfileNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', TomSelectType::class, [
                'label' => 'profile.note.category',
                'required' => false,
                'allow_create' => true,
                'choices' => $options['categories'],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('comment', CkEditorType::class, [
                'label' => 'profile.note.comment',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ProfileNote::class,
                'categories' => [],
            ])
        ;
    }
}
