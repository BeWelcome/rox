<?php

namespace App\Form;

use App\Entity\Preference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PreferencesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Preference[] $preferences */
        $preferences = $options['preferences'];

        foreach ($preferences as $preference) {
            $choices = $this->getChoices($preference);
            if (2 === \count($choices)) {
                $builder
                    ->add($preference->getCodename(), SwitchType::class, [
                        'label' => strtolower($preference->getCodename()),
                        'help' => strtolower($preference->getCodedescription()),
                        'help_html' => true,
                        'choices' => $choices,
                        'default' => $preference->getDefaultValue(),
                    ])
                ;
            } else {
                $builder
                    ->add($preference->getCodename(), ChoiceType::class, [
                        'attr' => [
                            'class' => 'preference',
                        ],
                        'label' => strtolower($preference->getCodename()),
                        'help' => strtolower($preference->getCodedescription()),
                        'expanded' => false,
                        'help_html' => true,
                        'choices' => $choices,
                        'multiple' => false,
                        'required' => true,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ])
                ;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'error_bubbling' => false,
                'preferences' => [],
            ])
            ->addAllowedTypes('preferences', 'array')
        ;
    }

    private function getChoices(Preference $preference): array
    {
        $possibleValues = $preference->getPossibleValues();
        $values = [];
        foreach ($possibleValues as $value) {
            $values[strtolower($preference->getCodename() . $value)] = $value;
        }

        return $values;
    }
}
