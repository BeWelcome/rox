<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationHost extends HostingRequestAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $form->add('cancel', SubmitType::class, [
                'label' => 'label.invitation.cancel',
            ]);
            $form->add('update', SubmitType::class, [
                'label' => 'label.hosting.update',
            ]);
            $this->addMessageTextArea($form, 'invitation.enter.message.for.guest');
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Message::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'invitation';
    }
}
