<?php

namespace App\Form;

use App\Entity\HostingRequest;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\DataTransformer\FlexibleTransformer;
use App\Form\DataTransformer\LegTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class HostingRequestType extends AbstractType
{
    private DateTimeTransformer $dateTimeTransformer;
    private LegTransformer $legTransformer;

    public function __construct(
        DateTimeTransformer $dateTimeTransformer,
        LegTransformer $legTransformer
    ) {
        $this->dateTimeTransformer = $dateTimeTransformer;
        $this->legTransformer = $legTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('arrival', HiddenType::class)
            ->add('departure', HiddenType::class)
            ->add('inviteForLeg', HiddenType::class)
            ->add('flexible', CheckboxType::class, [
                'label' => 'label.flexible',
                'required' => false,
            ])
        ;
        $builder
            ->get('arrival')
            ->addModelTransformer($this->dateTimeTransformer);
        $builder
            ->get('departure')
            ->addModelTransformer($this->dateTimeTransformer);
        $builder
            ->get('inviteForLeg')
            ->addModelTransformer($this->legTransformer);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $numberOfTravellersOptions = [
                'label' => 'request.number_of_travellers',
                'attr' => [
                    'placeholder' => 'placeholder.request.nbtravellers',
                    'min' => 1,
                    'max' => 20,
                ],
                'invalid_message' => 'request.error.number_of_travellers',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'request.error.numberoftravellers.empty',
                    ]),
                    new LessThanOrEqual(20),
                    new GreaterThanOrEqual(1),
                ],
            ];
            $durationOptions = [
                'required' => false,
                'mapped' => false,
                'invalid_message' => 'request.error.duration',
                'constraints' => [
                    new NotBlank(),
                ],
            ];

            $form = $event->getForm();
            $options = $form->getConfig()->getOptions();
            $data = $event->getData();
            if (null !== $data) {
                if ($options['reply_host']) {
                    if (!$data->getFlexible()) {
                        $durationOptions['disabled'] = true;
                    }
                    $form->remove('flexible');
                }
            }
            $form->add('duration', TextType::class, $durationOptions);

            if (true === $options['invitation']) {
                $form->add('numberOfTravellers', HiddenType::class);
                $form->remove('flexible');
            } else {
                $form->add('numberOfTravellers', IntegerType::class, $numberOfTravellersOptions);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => HostingRequest::class,
                'reply_guest' => false,
                'reply_host' => false,
                'new_request' => false,
                'invitation' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'request';
    }
}
