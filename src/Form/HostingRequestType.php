<?php

namespace App\Form;

use App\Entity\HostingRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class HostingRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('arrival', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'datepicker',
                    'placeholder' => 'Arrival (date)',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please provide an arrival date.',
                    ]),
                    new Date([
                        'message' => 'Please provide an arrival date.',
                    ]),
                ],
            ])
            ->add('departure', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'datepicker',
                    'placeholder' => 'Departure (date)',
                ],
            ])
            ->add('flexible', CheckboxType::class, [
                'required' => false,
            ])
            ->add(
                'numberOfTravellers',
                IntegerType::class,
                [
                    'empty_data' => 1,
                    'label' => 'Number of travellers',
                    'attr' => [
                        'placeholder' => '#',
                        'class' => 'ml-2 p-2 travellersnr',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please provide number of travellers.',
                        ]),
                        new LessThanOrEqual(10),
                        new GreaterThanOrEqual(1),
                    ],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => HostingRequest::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'App_request';
    }
}
