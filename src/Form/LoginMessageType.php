<?php

namespace App\Form;

use App\Entity\LoginMessage;
use App\Form\DataTransformer\DateTimeTransformer;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginMessageType extends AbstractType
{
    private DateTimeTransformer $dateTimeTransformer;

    public function __construct(DateTimeTransformer $dateTimeTransformer)
    {
        $this->dateTimeTransformer = $dateTimeTransformer;
    }
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', CkEditorType::class, [
                'label' => 'Login message',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('expires', TextType::class, [
                'attr' => [
                    'class' => 'flatpickr',
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ])
        ;
        $builder
            ->get('expires')
            ->addModelTransformer($this->dateTimeTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => LoginMessage::class,
            ])
        ;
    }
}
