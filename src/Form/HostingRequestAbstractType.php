<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

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

    protected function addMessageTextArea(FormInterface $form, $placeholder)
    {
        $form
            ->add('message', CkEditorType::class, [
                'attr' => [
                    'placeholder' => $placeholder,
                    'class' => 'editor mb-1',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please provide a message text.',
                    ]),
                ],
            ]);
    }
}
