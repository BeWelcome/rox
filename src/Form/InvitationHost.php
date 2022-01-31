<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class InvitationHost extends HostingRequestAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('request', HostingRequestType::class, [
                'invitation' => true,
                'reply_guest' => true,
            ])
            ->add('message', CkEditorType::class, [
                'label' => 'label.message',
                'invalid_message' => 'request.message.empty',
                'attr' => [
                    'placeholder' => 'invitation.enter.message.for.guest',
                    'class' => 'editor form-control',
                ],
            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $messageRequest = $event->getData();
            $form = $event->getForm();
            if (!$messageRequest || null === $messageRequest->getSubject()) {
                $form->add('send', SubmitType::class, [
                    'label' => 'label.invitation.send',
                ]);
                $form->add('subject', SubjectType::class);
            } else {
                $form->add('cancel', SubmitType::class, [
                    'label' => 'label.invitation.cancel',
                ]);
                $form->add('update', SubmitType::class, [
                    'label' => 'label.hosting.update',
                ]);
            }
        });
    }
}
