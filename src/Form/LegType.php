<?php

namespace App\Form;

use App\Doctrine\LegOptionsType;
use App\Dto\LegDto;
use App\Form\DataTransformer\DateTransformer;
use App\Form\DataTransformer\LocationTransformer;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class LegType extends AbstractType
{
    public function __construct(
        private readonly DateTransformer $transformer,
        private readonly LocationTransformer $locationTransformer,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

        $expired = false;
        $data = $builder->getData();
        if (null !== $data) {
            $departure = $data->departure ?? null;
            if ($departure instanceof DateTime && $departure <= new DateTime()) {
                $expired = true;
            }
        }

        $builder
            ->add('location', SearchLocationType::class, [
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
                    'trip.option.private' => LegOptionsType::PRIVATE,
                    'trip.option.looking.for.host' => LegOptionsType::LOOKING_FOR_HOST,
                    'trip.option.meet.locals' => LegOptionsType::MEET_LOCALS,
                ],
                'multiple' => true,
                'expanded' => true,
                'disabled' => $expired,
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
            ]);

        $builder
            ->get('location')
            ->addModelTransformer($this->locationTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LegDto::class,
            'error_bubbling' => false,
            'error_mapping' => [
                'arrival' => 'duration',
            ],
        ]);
    }
}
