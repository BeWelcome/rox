<?php

namespace App\Form\Admin;

use Locale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * The name of a group category in one language. For a new category it's the English name; on an existing one an
 * empty name removes the language (but not English).
 */
class GroupCategoryNameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['new']) {
            $builder->add('name', TextType::class, [
                'label' => 'name',
                'constraints' => [new NotBlank(), new Length(max: 100)],
            ]);
        } else {
            $displayLocale = $options['display_locale'];
            $choices = [];
            foreach ($options['locales'] as $locale) {
                $choices[Locale::getDisplayName($locale, $displayLocale) . " ({$locale})"] = $locale;
            }
            ksort($choices, \SORT_NATURAL | \SORT_FLAG_CASE);

            $builder
                ->add('locale', ChoiceType::class, [
                    'label' => 'language',
                    'choices' => $choices,
                    'attr' => ['class' => 'js-tom-select'],
                    'constraints' => [new NotBlank()],
                ])
                ->add('name', TextType::class, [
                    'label' => 'name',
                    'required' => false,
                    'help' => 'admin.group.category.name.help',
                    'constraints' => [new Length(max: 100)],
                ]);
        }

        $builder->add('save', SubmitType::class, ['label' => $options['new'] ? 'add' : 'save']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'new' => false,
            'locales' => [],
            'display_locale' => 'en',
        ]);
        $resolver->setAllowedTypes('new', 'bool');
        $resolver->setAllowedTypes('locales', 'array');
        $resolver->setAllowedTypes('display_locale', 'string');
    }
}
