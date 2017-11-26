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
use Symfony\Component\Validator\Constraints\NotBlank;

class HostingRequestGuest extends AbstractType
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
                    'config' => [
                        'extraPlugins' => 'confighelper',
                    ],
                    'plugins' => [
                        'confighelper' => [
                            'path' => '/bundles/app/js/confighelper/',
                            'filename' => 'plugin.js',
                        ],
                    ],
                    'attr' => [
                        'placeholder' => 'Please leave a message after the beep',
                        'class' => 'mb-1',
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $messageRequest = $event->getData();
            $form = $event->getForm();
            if (!$messageRequest || null === $messageRequest->getSubject()) {
                $form->add('send', SubmitType::class);
            } else {
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
