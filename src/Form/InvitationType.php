<?php

namespace App\Form;

use App\Entity\Message;
use App\Form\DataTransformer\DateTimeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationType extends AbstractType
{
    private DateTimeTransformer $transformer;

    public function __construct(DateTimeTransformer $transformer)
    {
        $this->transformer = $transformer;
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
        return 'app_message';
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
            ])
            ->add('message', CkEditorType::class, [
                'label' => 'label.message',
                'invalid_message' => 'request.message.empty',
                'attr' => [
                    'placeholder' => 'invitation.message.placeholder',
                    'class' => 'editor',
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'label.invitation.send',
            ])
        ;
    }
}
