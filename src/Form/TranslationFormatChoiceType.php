<?php

namespace App\Form;

use App\Doctrine\TranslationFormat;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The format of an English text (TranslationFormat): html, inline or plain, or nothing to have it detected from the
 * text. Holds the value as a string, empty for detected.
 */
final class TranslationFormatChoiceType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [];
        foreach (TranslationFormat::cases() as $format) {
            $choices['translation.format.' . $format->value] = $format->value;
        }

        $resolver->setDefaults([
            'label' => 'translation.format',
            'choices' => $choices,
            'placeholder' => 'translation.format.detect',
            'required' => false,
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
