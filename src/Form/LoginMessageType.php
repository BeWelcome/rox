<?php

namespace App\Form;

use App\Entity\LoginMessage;
use App\Form\DataTransformer\DateTimeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginMessageType extends AbstractType
{
    public function __construct(private readonly DateTimeTransformer $dateTimeTransformer)
    {
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', CkEditorType::class, [
                'label' => 'admin.tools.login_message.message',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('expires', TextType::class, [
                'label' => 'admin.tools.login_message.expires',
                'attr' => [
                    'class' => 'flatpickr',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
        $builder
            ->get('expires')
            ->addModelTransformer($this->dateTimeTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => LoginMessage::class,
            ])
        ;
    }
}
