<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HostingRequestGuest extends HostingRequestAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('request', HostingRequestType::class, [
                'reply_guest' => true,
            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $messageRequest = $event->getData();
            $form = $event->getForm();
            if (!$messageRequest || null === $messageRequest->getSubject()) {
                $this->addMessageTextArea(
                    $form,
                    'Please give a short introduction of yourself and let your host know '.
                    'when and how you\'re going to arrive.'
                );
                $form->add('send', SubmitType::class);
                $form->add('subject', SubjectType::class);
            } else {
                $this->addMessageTextArea($form, 'Please enter a message for your host.');
                $form->add('cancel', SubmitType::class);
                $form->add('update', SubmitType::class);
            }
        });
    }
}
