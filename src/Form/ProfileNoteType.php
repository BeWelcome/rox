<?php

namespace App\Form;

use App\Entity\ProfileNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // one or more categories: the type-ahead writes them into the field separated by commas
            ->add('categories', TextType::class, [
                'label' => 'profile.note.category',
                'property_path' => 'categoryNames',
                'required' => false,
                'autocomplete' => true,
                'autocomplete_choices' => $options['categories'],
                'allow_options_create' => true,
                'constraints' => [
                    new Count(min: 1, minMessage: 'profile.note.categories.needed'),
                ],
            ])
            ->add('comment', CkEditorType::class, [
                'label' => 'profile.note.comment',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
        $builder->get('categories')->addModelTransformer(new CallbackTransformer(
            static fn (?array $names): string => implode(',', $names ?? []),
            static fn (?string $value): array => array_values(array_filter(array_map('trim', explode(',', (string) $value)), static fn (string $name): bool => '' !== $name)),
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ProfileNote::class,
                'categories' => [],
            ])
        ;
    }
}
