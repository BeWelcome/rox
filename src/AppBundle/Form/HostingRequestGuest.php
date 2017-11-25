<?php

namespace AppBundle\Form;

use AppBundle\Entity\Message;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                'constraints' => [
                        new NotBlank()
                    ]
                ])
        ;
        $builder->add('send', SubmitType::class);
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
