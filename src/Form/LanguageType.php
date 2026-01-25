<?php

namespace App\Form;

use App\Form\ChoiceLoader\LanguageChoiceLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class LanguageType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choiceLoader = function (Options $options): ChoiceLoaderInterface {
            return ChoiceList::loader($this, new LanguageChoiceLoader($options, $this->entityManager));
        };

        $resolver->setDefaults([
            'choice_loader' => $choiceLoader,
            'choice_label' => 'name',
            'choice_value' => 'shortcode',
            'label' => 'label.language',
            'label_translation_domain' => 'messages',
            'error_bubbling' => false,
            'multiple' => false,
            'autocomplete' => true,
            'help' => 'help.language',
            'required' => false,

            'constraints' => [
                new NotNull(message: 'error.language'),
            ],
            'written' => false,
            'spoken' => false,
            'signed' => false,
        ]);

        $resolver->setAllowedTypes('written', 'bool');
        $resolver->setAllowedTypes('spoken', 'bool');
        $resolver->setAllowedTypes('signed', 'bool');
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
