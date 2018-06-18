<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HostingRequestHost extends HostingRequestAbstractType
{
    /**
     * {@inheritdoc}
     *
     * Used for a reply of a host.
     *
     * Offers the possibility to change the dates and process tha request (accept, cancel)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', SubjectType::class)
            ->add('request', HostingRequestType::class)
            ->add('decline', SubmitType::class)
            ->add('tentatively', SubmitType::class)
            ->add('accept', SubmitType::class)
            ->add('update', SubmitType::class);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $this->addMessageTextArea($form, 'Please enter a message for your guest.');
        });
    }
}
