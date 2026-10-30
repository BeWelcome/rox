<?php

namespace App\Form;

use Locale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * A forum post: a new thread (with its title), a reply, or an edit or translation of a post (with its language).
 * Data: title, text, visibility, locale, subscribe.
 */
class ForumPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['with_title']) {
            $builder->add('title', TextType::class, [
                'label' => 'forum_label_topictitle',
                'constraints' => [new NotBlank(), new Length(max: 200)],
            ]);
        }

        $builder->add('text', CkEditorType::class, [
            'label' => 'forum_label_text',
            'constraints' => [new NotBlank()],
        ]);

        if (null !== $options['locales']) {
            $choices = [];
            foreach ($options['locales'] as $locale) {
                $choices[Locale::getDisplayName($locale, $options['display_locale']) . " ({$locale})"] = $locale;
            }
            ksort($choices, \SORT_NATURAL | \SORT_FLAG_CASE);
            $builder->add('locale', ChoiceType::class, [
                'label' => 'forum_label_lang',
                'choices' => $choices,
                'choice_translation_domain' => false,
                'attr' => ['class' => 'js-tom-select'],
            ]);
        }

        if ([] !== $options['visibilities']) {
            $choices = [];
            foreach ($options['visibilities'] as $visibility) {
                $choices['forum_edit_vis_' . strtolower($visibility)] = $visibility;
            }
            $builder->add('visibility', ChoiceType::class, [
                'label' => 'forum_label_visibility',
                'choices' => $choices,
                'expanded' => true,
            ]);
        }

        if ($options['with_subscribe']) {
            $builder->add('subscribe', CheckboxType::class, [
                'label' => 'forum.post.subscribe',
                'required' => false,
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => $options['submit_label'],
            'attr' => ['class' => 'btn btn-primary'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'with_title' => false,
            'with_subscribe' => true,
            'visibilities' => [],
            'locales' => null,
            'display_locale' => 'en',
            'submit_label' => 'forum_label_create_post',
        ]);
        $resolver->setAllowedTypes('submit_label', 'string');
        $resolver->setAllowedTypes('with_title', 'bool');
        $resolver->setAllowedTypes('with_subscribe', 'bool');
        $resolver->setAllowedTypes('visibilities', 'array');
        $resolver->setAllowedTypes('locales', ['null', 'array']);
        $resolver->setAllowedTypes('display_locale', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'forum_post';
    }
}
