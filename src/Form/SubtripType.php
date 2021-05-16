<?php

namespace App\Form;

use App\Doctrine\SubtripOptionsType;
use App\Entity\Subtrip;
use App\Form\DataTransformer\DateTimeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

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
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('location', SearchLocationType::class)
            ->add('duration', TextType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('arrival', HiddenType::class)
            ->add('departure', HiddenType::class)
            ->add('options', ChoiceType::class, [
                'choices' => [
                    'trip.option.private' => SubtripOptionsType::PRIVATE,
                    'trip.option.looking.for.host' => SubtripOptionsType::LOOKING_FOR_HOST,
                    'trip.option.meet.locals' => SubtripOptionsType::MEET_LOCALS,
                ],
                'multiple' => true,
                'expanded' => true,
            ])
        ;

        $formBuilder
            ->get('arrival')
            ->addModelTransformer($this->transformer)
        ;
        $formBuilder
            ->get('departure')
            ->addModelTransformer($this->transformer)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Subtrip::class,
        ]);
    }
}
