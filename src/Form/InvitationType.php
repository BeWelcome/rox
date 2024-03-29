<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class InvitationType extends HostingRequestAbstractType
{
    public function getBlockPrefix(): string
    {
        return 'invitation';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', SubjectType::class)
            ->add('request', HostingRequestType::class, [
                'invitation' => true,
                'new' => true,
            ])
            ->add('message', CkEditorType::class, [
                'label' => 'label.message',
                'attr' => [
                    'placeholder' => 'invitation.message.placeholder',
                    'class' => 'editor',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'please.enter.a.message.text',
                    ]),
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'label.invitation.send',
            ])
        ;
    }
}
