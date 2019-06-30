<?php

namespace App\Form;

use App\Entity\HostingRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $flexibleOptions = [
                'label' => 'label.flexible',
                'required' => false,
            ];
            $arrivalOptions = [
                'label' => 'request.arrival',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'datepicker',
                    'placeholder' => 'placeholder.arrival',
                ],
                'invalid_message' => 'request.error.arrival.no_date',
                'constraints' => [
                    new NotBlank([
                        'message' => 'request.error.arrival.empty',
                    ]),
                    new LessThanOrEqual([
                        'propertyPath' => 'parent.all[departure].data',
                        'message' => 'request.error.arrival.after.departure',
                    ]),
                ],
            ];
            $departureOptions = [
                'label' => 'request.departure',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'datepicker',
                    'placeholder' => 'placeholder.departure',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'request.error.departure.empty',
                    ]),
                ],
            ];
            $numberOfTravellersOptions = [
                'label' => 'request.number_of_travellers',
                'attr' => [
                    'placeholder' => 'placeholder.request.nbtravellers',
                    'min' => 1,
                    'max' => 20,
                ],
                'invalid_message' => 'request.error.number_of_travellers',
                'constraints' => [
                    new LessThanOrEqual(20),
                    new GreaterThanOrEqual(1),
                ],
            ];
            $form = $event->getForm();
            $options = $form->getConfig()->getOptions();
            $data = $event->getData();
            if (null !== $data) {
                if ($options['reply_host'] && !$data->getFlexible()) {
                    $arrivalOptions['disabled'] = true;
                    $departureOptions['disabled'] = true;
                    $flexibleOptions['disabled'] = true;
                    $numberOfTravellersOptions['disabled'] = true;
                }
            }
            $form->add('arrival', DateType::class, $arrivalOptions);
            $form->add('departure', DateType::class, $departureOptions);
            $form->add('flexible', CheckboxType::class, $flexibleOptions);
            $form->add('numberOfTravellers', IntegerType::class, $numberOfTravellersOptions);
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
