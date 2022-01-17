<?php

namespace App\Form;

use App\Doctrine\SubtripOptionsType;
use App\Entity\Subtrip;
use App\Form\DataTransformer\DateTimeTransformer;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class SubtripType extends AbstractType
{
    private DateTimeTransformer $transformer;

    public function __construct(DateTimeTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('arrival', HiddenType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('departure', HiddenType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;

        $builder
            ->get('arrival')
            ->addModelTransformer($this->transformer);

        $builder
            ->get('departure')
            ->addModelTransformer($this->transformer);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $locationRequest = $event->getData();
            $form = $event->getForm();

            $expired = false;
            if (null !== $locationRequest) {
                $arrival = $locationRequest->getArrival();
                if (null !== $arrival && $arrival <= new DateTime()) {
                    $expired = true;
                }
            }
            $form->add('location', SearchLocationType::class, [
                    'expired' => $expired,
                    'label' => 'location',
                ])
                ->add('duration', TextType::class, [
                    'required' => false,
                    'mapped' => false,
                    'disabled' => $expired,
                    'label' => 'duration',
                ])
                ->add('options', ChoiceType::class, [
                    'choices' => [
                        'trip.option.private' => SubtripOptionsType::PRIVATE,
                        'trip.option.looking.for.host' => SubtripOptionsType::LOOKING_FOR_HOST,
                        'trip.option.meet.locals' => SubtripOptionsType::MEET_LOCALS,
                    ],
                    'multiple' => true,
                    'expanded' => true,
                    'disabled' => $expired,
                    'constraints' => [
                        new NotBlank(),
                        new NotNull(),
                    ]
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Subtrip::class,
            'error_bubbling' => false,
            'error_mapping' => [
                'arrival' => 'duration',
                'departure' => 'duration',
            ]
        ]);
    }
}
