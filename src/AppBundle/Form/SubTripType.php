<?php

namespace AppBundle\Form;

use AppBundle\Entity\SubTrip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubTripType extends AbstractType
{
    /**
     * @param FormBuilderInterface $formBuilder
     * @param array                $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('search', TextType::class, [
                'attr' => [
                    'class' => 'search-picker',
                ],
                'mapped' => false,
            ])
            ->add('search_geoname_id', HiddenType::class)
            ->add('search_latitude', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('search_longitude', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('arrival', DateType::class)
            ->add('departure', DateType::class)
            ->add('options', ChoiceType::class, [
                'choices' => [
                    'TripsLocationOptionLookingForAHost' => 1,
                    'TripsLocationOptionLikeToMeetUp' => 2,
                ],
                'multiple' => true,
                'expanded' => true,
                'label' => 'Additional Info',
            ]);

        $formBuilder->get('options')
            ->addModelTransformer(new CallbackTransformer(
                function ($optionsAsNumber) {
                    // transform the number back to an array
                    $optionsAsArray = [];
                    if (($optionsAsNumber & 1) === 1) {
                        $optionsAsArray['TripsLocationOptionLookingForAHost'] = 1;
                    }
                    if (($optionsAsNumber & 2) === 2) {
                        $optionsAsArray['TripsLocationOptionLikeToMeetUp'] = 2;
                    }

                    return $optionsAsArray;
                },
                function ($optionsAsArray) {
                    // transform the array to a number
                    if (null === $optionsAsArray) {
                        return 0;
                    }
                    $optionsAsNumber = 0;
                    foreach ($optionsAsArray as $value) {
                        $optionsAsNumber += $value;
                    }

                    return $optionsAsNumber;
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubTrip::class,
        ]);
    }
}
