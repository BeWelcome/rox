<?php

namespace App\Form;

use App\Form\CustomDataClass\WikiPageRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The wiki editor: the markup (the visual mode writes it before sending) and the version the edit started from.
 */
class WikiPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'wiki.editor.markup',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('version', HiddenType::class);

        $builder->get('version')->addModelTransformer(new CallbackTransformer(
            static fn (?int $version): string => (string) ($version ?? 0),
            static fn (?string $version): int => (int) $version,
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WikiPageRequest::class,
        ]);
    }
}
