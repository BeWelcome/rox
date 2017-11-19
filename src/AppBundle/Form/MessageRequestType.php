<?php

namespace AppBundle\Form;

use AppBundle\Entity\Message;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', SubjectType::class)
            ->add('request', HostingRequestType::class)
            ->add('message', CKEditorType::class, [
                    'attr' => [
                        'placeholder' => 'Give a short explanation...',
                        'class' => 'w-100 p-2',
                    ],
                ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $messageRequest = $event->getData();
            $form = $event->getForm();

            if (!$messageRequest || null === $messageRequest->getId()) {
                $form->add('send', SubmitType::class);
            }
        });
        if (isset($options['reply'])) {
            if ($options['owner']) {
                $builder
                    ->add('cancel', SubmitType::class);
            } else {
                $builder
                    ->add('accept', SubmitType::class)
                    ->add('tentatively', SubmitType::class)
                    ->add('decline', SubmitType::class);
            }
            $builder
                ->add('update', SubmitType::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(['owner', 'reply'])
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
