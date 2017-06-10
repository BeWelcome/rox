<?php

namespace AppBundle\Form;

use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HostingRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('arrival', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => \IntlDateFormatter::FULL,
                'attr' => ['class' => 'datepicker'],
            ])
            ->add('departure', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'html5' => false,
                'format' => \IntlDateFormatter::FULL,
                'attr' => ['class' => 'datepicker'],
            ])
            ->add('flexible', CheckboxType::class, [
                'required' => false,
            ])
            ->add('numberOfTravellers');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\HostingRequest',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_hosting_request';
    }
}
