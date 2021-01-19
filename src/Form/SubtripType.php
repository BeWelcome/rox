<?php

namespace App\Form;

use App\Doctrine\SubtripOptionsType;
use App\Entity\Subtrip;
use App\Form\DataTransformer\SubtripOptionsTypeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SubtripType extends AbstractType
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
            ->add('arrival', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
            ])
            ->add('departure', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
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
            'data_class' => Subtrip::class,
        ]);
    }
}
