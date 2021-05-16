<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class InvitationGuest extends HostingRequestAbstractType
{
    /**
     * {@inheritdoc}
     *
     * Used for a reply of a host.
     *
     * Offers the possibility to change the dates and process the request (accept, cancel)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('request', HostingRequestType::class, [
                'invitation' => true,
                'reply_host' => true,
            ])
            ->add('decline', SubmitType::class, [
                'label' => 'label.hosting.decline',
            ])
            ->add('tentatively', SubmitType::class, [
                'label' => 'label.hosting.tentatively',
            ])
            ->add('accept', SubmitType::class, [
                'label' => 'label.hosting.accept',
            ])
            ->add('update', SubmitType::class, [
                'label' => 'label.hosting.update',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $this->addMessageTextArea($form, 'enter.message.for.host');
//            $form->add('subject', SubjectType::class, ['disabled' => true]);
        });
    }
}
