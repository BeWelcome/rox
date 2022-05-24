<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class HostingRequestAbstractType extends AbstractType
{
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
        return 'app_message';
    }

    protected function addMessageTextArea(FormInterface $form, string $placeholder)
    {
        $form
            ->add('message', CkEditorType::class, [
                'label' => 'label.message',
                'attr' => [
                    'placeholder' => $placeholder,
                    'class' => 'editor form-control',
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'please.enter.a.message.text',
                    ]),
                    new NotBlank([
                        'message' => 'please.enter.a.message.text',
                    ]),
                ],
                'empty_data' => '',
            ])
        ;
    }
}
