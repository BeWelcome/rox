<?php

namespace App\Form;

use Override;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class InvitationType extends HostingRequestAbstractType
{
    #[Override]
    public function getBlockPrefix(): string
    {
        return 'invitation';
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', SubjectType::class)
            ->add('request', HostingRequestType::class, [
                'invitation' => true,
                'new' => true,
            ])
            ->add('message', CkEditorType::class, [
                // member-written HTML: purified as it is shown (purify('custom') in the templates)
                'purify_html' => true,
                'purify_html_profile' => 'custom',
                'label' => 'label.message',
                'attr' => [
                    'placeholder' => 'invitation.message.placeholder',
                    'class' => 'editor',
                ],
                'constraints' => [
                    new NotBlank(message: 'please.enter.a.message.text'),
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'label.invitation.send',
            ])
        ;
    }
}
