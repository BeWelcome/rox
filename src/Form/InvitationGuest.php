<?php

namespace App\Form;

use App\Entity\Message;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\DataTransformer\LegTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class InvitationGuest extends HostingRequestAbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', CkEditorType::class, [
                'label' => 'label.message',
                'attr' => [
                    'placeholder' => 'invitation.enter.message.for.host',
                    'class' => 'editor form-control',
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'please.enter.a.message.text',
                    ]),
                    new NotBlank([
                        'message' => 'please.enter.a.message.text',
                    ]),
                ],
                'empty_data' => '',
            ])
            ->add('request', HostingRequestType::class, [
                'invitation' => true,
                'reply_guest' => true,
            ])
            ->add('decline', SubmitType::class, [
                'label' => 'label.hosting.decline',
            ])
            ->add('update', SubmitType::class, [
                'label' => 'label.hosting.update',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $options = $form->getConfig()->getOptions();
            if (!$options['already_accepted']) {
                $form
                    ->add('tentatively', SubmitType::class, [
                        'label' => 'label.hosting.tentatively',
                    ])
                    ->add('accept', SubmitType::class, [
                        'label' => 'label.hosting.accept',
                    ])
                ;
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
                'data_class' => Message::class,
                'already_accepted' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'invitation';
    }
}
