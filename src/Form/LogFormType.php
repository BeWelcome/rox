<?php

namespace App\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LogFormType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('types', ChoiceType::class, [
                'choices' => $options['data']['logTypes'],
                'choice_translation_domain' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'class' => 'select2',
                ],
            ])
            // a member search with suggestions in the template (member/suggest)
            ->add('username', TextType::class, [
                'required' => false,
            ]);
    }

    /**
     * A filter in the URL (GET): nothing to protect, and the page builds its own fields (admin/logs/index.html.twig).
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'log';
    }
}
