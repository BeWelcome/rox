<?php

namespace App\Form;

use App\Entity\HostingRequest;
use App\Form\DataTransformer\DateTransformer;
use App\Form\DataTransformer\LegTransformer;
use Closure;
use DateTime;
use DateTimeInterface;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class HostingRequestType extends AbstractType
{
    public function __construct(
        private readonly DateTransformer $dateTimeTransformer,
        private readonly LegTransformer $legTransformer,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // an empty date stays off the request (the entity takes no null): the duration's check says it is missing
            ->add('arrival', HiddenType::class, [
                'setter' => static function (HostingRequest $request, ?DateTime $arrival): void {
                    if (null !== $arrival) {
                        $request->setArrival($arrival);
                    }
                },
            ])
            ->add('departure', HiddenType::class, [
                'setter' => static function (HostingRequest $request, ?DateTime $departure): void {
                    if (null !== $departure) {
                        $request->setDeparture($departure);
                    }
                },
            ])
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
            $form = $event->getForm();
            $options = $form->getConfig()->getOptions();
            $data = $event->getData();

            if ($options['invitation']) {
                $this->addFieldsForInvitation($form, $options);
            }
            if ($options['request']) {
                $this->addFieldsForRequest($form, $options, $data);
            }
        });

        // No stay in the past: a request (or an invitation) whose arrival has passed can only be answered by a message
        // (BaseRequestModel::hasExpired). Checked for new dates only, so a message on a request keeps its dates.
        $arrivalBefore = null;
        $builder->addEventListener(FormEvents::POST_SET_DATA, static function (FormEvent $event) use (&$arrivalBefore) {
            $data = $event->getData();
            $arrivalBefore = $data instanceof HostingRequest ? $data->getArrival()?->format('Y-m-d') : null;
        });
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use (&$arrivalBefore) {
            $arrival = $event->getForm()->get('arrival')->getData();
            if (!$arrival instanceof DateTimeInterface) {
                return;
            }
            $day = $arrival->format('Y-m-d');
            if ($day < date('Y-m-d') && $day !== $arrivalBefore) {
                $form = $event->getForm();
                $field = $form->has('duration') ? $form->get('duration') : $form;
                $field->addError(new FormError($this->translator->trans('request.error.arrival.past')));
            }
        });
        // The duration field only mirrors the two dates (filled by requests.js). Fill it here
        // too, so that a request with both dates never fails on "Please set a duration".
        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $data = $event->getData();
            if (!\is_array($data) || !$event->getForm()->has('duration')) {
                return;
            }
            $arrival = trim((string) ($data['arrival'] ?? ''));
            $departure = trim((string) ($data['departure'] ?? ''));
            if ('' === trim((string) ($data['duration'] ?? '')) && '' !== $arrival && '' !== $departure) {
                $data['duration'] = $arrival . ' - ' . $departure;
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => HostingRequest::class,
                'reply_guest' => false,
                'reply_host' => false,
                'request' => false,
                'invitation' => false,
                'new' => false,
            ])
        ;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'request';
    }

    /**
     * An empty number of travellers stays off the request (the entity takes no null): the field's check says so.
     */
    private static function travellersSetter(): Closure
    {
        return static function (HostingRequest $request, mixed $travellers): void {
            if (null !== $travellers && '' !== $travellers) {
                $request->setNumberOfTravellers((int) $travellers);
            }
        };
    }

    private function addFieldsForInvitation(FormInterface $form, array $options): void
    {
        if ($options['new']) {
            $form->add('duration', TextType::class, [
                'required' => false,
                'label' => 'duration',
                'mapped' => false,
                'invalid_message' => 'request.error.duration',
                'constraints' => [
                    new NotBlank(message: 'request.error.duration'),
                ],
            ]);
        }
        $form->remove('flexible');

        $form->add('numberOfTravellers', HiddenType::class, ['setter' => self::travellersSetter()]);
    }

    private function addFieldsForRequest(FormInterface $form, array $options, $data): void
    {
        $numberOfTravellersOptions = [
            'label' => 'request.number_of_travellers',
            'attr' => [
                'placeholder' => 'placeholder.request.nbtravellers',
                'min' => 1,
                'max' => 20,
            ],
            // not a whole number ("abc", "2 people", "1.5"), or out of the range
            'invalid_message' => 'request.error.numberoftravellers.range',
            'required' => false,
            'setter' => self::travellersSetter(),
            'constraints' => [
                new NotBlank(message: 'request.error.numberoftravellers.empty'),
                new LessThanOrEqual(20, message: 'request.error.numberoftravellers.range'),
                new GreaterThanOrEqual(1, message: 'request.error.numberoftravellers.range'),
            ],
        ];

        $durationOptions = [
            'required' => false,
            'label' => 'duration',
            'mapped' => false,
        ];

        // a whole number the member enters: the field starts empty, there is no default
        $numberOfTravellersType = IntegerType::class;
        if (null !== $data) {
            if ($options['reply_host']) {
                $numberOfTravellersType = HiddenType::class;
                if (!$data->getFlexible()) {
                    $durationOptions['disabled'] = true;
                    $form->remove('flexible');
                }
            }
        } else {
            $durationOptions['invalid_message'] = 'request.error.duration';
            $durationOptions['constraints'] = [
                new NotBlank(message: 'request.error.duration'),
            ];
        }
        $form->add('duration', TextType::class, $durationOptions);

        $form->add('numberOfTravellers', $numberOfTravellersType, $numberOfTravellersOptions);
    }
}
