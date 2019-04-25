<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MessageToMemberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', CkEditorType::class, [
                'attr' => [
                    'placeholder' => 'Please enter a message.',
                    'class' => 'editor mb-1',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a message text.',
                    ]),
                ],
            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Message $message */
            $message = $event->getData();
            $form = $event->getForm();
            if ($message && ($message->getSubject() !== null)) {
                // set subject to read only if one exists already
                $form->add('subject', SubjectType::class, ['disabled' => true]);
            } else {
                $form->add('subject', SubjectType::class);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Message',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'App_message';
    }
}
