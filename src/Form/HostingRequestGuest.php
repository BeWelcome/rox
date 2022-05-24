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
                'request' => true,
                'reply_guest' => true,
            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $messageRequest = $event->getData();
            $form = $event->getForm();
            if (!$messageRequest || null === $messageRequest->getSubject()) {
                $this->addMessageTextArea(
                    $form,
                    'give.short.intro.yourself.let.host.know.when.how.you.arrive.'
                );
                $form->add('send', SubmitType::class, [
                    'label' => 'label.hosting.send',
                ]);
                $form->add('subject', SubjectType::class);
            } else {
                $this->addMessageTextArea($form, 'please.enter.a.message.for.your.host');
                $form->add('cancel', SubmitType::class, [
                    'label' => 'label.hosting.cancel',
                ]);
                $form->add('update', SubmitType::class, [
                    'label' => 'label.hosting.update',
                ]);
            }
        });
    }
}
