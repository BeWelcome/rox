<?php

namespace App\Form;

use HTMLPurifier;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SubjectType extends AbstractType
{
    public function __construct(
        #[Autowire(service: 'exercise_html_purifier.text')]
        private readonly HTMLPurifier $textPurifier,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('subject', TextType::class, [
            'label' => 'label.subject',
            'attr' => [
                'placeholder' => 'placeholder.subject',
            ],
            'required' => false,
            'constraints' => [
                new NotBlank(message: 'error.subject.blank'),
            ],
        ]);

        // A subject is plain text: markup is removed, characters stay (Twig escapes them on output)
        $builder->get('subject')->addModelTransformer(new CallbackTransformer(
            static fn (?string $subject): ?string => $subject,
            fn (?string $subject): ?string => null === $subject
                ? null
                : html_entity_decode($this->textPurifier->purify($subject), \ENT_QUOTES | \ENT_HTML5, 'UTF-8'),
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Subject::class,
        ]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'app_subject';
    }
}
