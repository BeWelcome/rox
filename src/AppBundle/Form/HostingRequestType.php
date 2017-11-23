<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
                    new NotBlank(),
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
                        'min' => 1,
                        'max' => 10,
                        'placeholder' => '#',
                        'class' => 'ml-2 p-2',
                    ],
                    'constraints' => [
                        new NotBlank(),
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
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\HostingRequest',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_hosting_request';
    }
}
