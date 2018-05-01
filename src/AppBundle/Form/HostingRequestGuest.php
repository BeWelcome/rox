<?php

namespace AppBundle\Form;

use AppBundle\Entity\Message;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class HostingRequestGuest extends HostingRequestType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', SubjectType::class)
            ->add('request', HostingRequestType::class)
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $messageRequest = $event->getData();
            $form = $event->getForm();
            if (!$messageRequest || null === $messageRequest->getSubject()) {
                $this->addMessageTextArea($form, 'Please give a short introduction of yourself and let your host know when and how you\'re going to arrive.');
                $form->add('send', SubmitType::class);
            } else {
                $this->addMessageTextArea($form, 'Please enter a message for your host.');
                $form->add('cancel', SubmitType::class);
                $form->add('update', SubmitType::class);
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
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_request';
    }
}
