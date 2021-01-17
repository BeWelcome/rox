<?php

namespace App\Form;

use App\Doctrine\SubtripOptionsType;
use App\Form\CustomDataClass\SubtripB;
use App\Form\DataTransformer\SubtripOptionsTypeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class SubtripTypeB extends AbstractType
{
    /**
     * @var SubtripOptionsTypeTransformer
     */
    private SubtripOptionsTypeTransformer $transformer;

    public function __construct(SubtripOptionsTypeTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('location', SearchLocationType::class)
            ->add('days', IntegerType::class, [
                'label' => 'trip.number_of_days',
                'attr' => [
                    'placeholder' => 'trip.number_of_days.placeholder',
                    'min' => 1,
                    'max' => 180,
                ],
                'invalid_message' => 'trip.error.number_of_days',
                'constraints' => [
                    new NotBlank([
                        'message' => 'trip.error.number_of_days.empty',
                    ]),
                    new LessThanOrEqual(180),
                    new GreaterThanOrEqual(1),
                ],
            ])
            ->add('options', ChoiceType::class, [
                'choices' => [
                    'trip.option.looking.for.host' => SubtripOptionsType::LOOKING_FOR_HOST,
                    'trip.option.meet.locals' => SubtripOptionsType::MEET_LOCALS,
                ],
                'multiple' => true,
                'expanded' => true,
                'label' => 'Additional Info',
            ]);

//        $formBuilder->get('options')
//            ->addModelTransformer($this->transformer)
//        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubtripB::class,
        ]);
    }
}
