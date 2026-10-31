<?php

namespace App\Form\Admin;

use App\Model\Admin\MassmailRights;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * One way to choose the recipients of a mass mail: members (all or by username), a place, a group, or all members
 * for a change of the terms of use.
 */
class MassmailRecipientsType extends AbstractType
{
    public const string ALL = 'all';
    public const string USERNAMES = 'usernames';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        switch ($options['recipients']) {
            case MassmailRights::MEMBERS:
                $builder
                    ->add('members', ChoiceType::class, [
                        'label' => false,
                        'choices' => [
                            'adminmassmailenqueueselectedmembers' => self::USERNAMES,
                            'adminmassmailenqueueallmembers' => self::ALL,
                        ],
                        'expanded' => true,
                        'data' => self::USERNAMES,
                    ])
                    ->add('usernames', TextType::class, [
                        'label' => 'adminmassmailenqueueusernames',
                        'help' => 'adminmassmailenqueueusernamesinfo',
                        'required' => false,
                    ])
                    ->add('limit', IntegerType::class, [
                        'label' => 'adminmassmailenqueuemaxmessages',
                        'required' => false,
                        'constraints' => [new GreaterThanOrEqual(0)],
                    ]);
                break;
            case MassmailRights::LOCATION:
                $builder
                    ->add('country', ChoiceType::class, [
                        'label' => 'country',
                        'choices' => $options['countries'],
                        'placeholder' => '',
                        'attr' => ['class' => 'js-tom-select'],
                        'constraints' => [new NotBlank()],
                    ])
                    // Filled by the page from the country (assets/js/admin/massmail.js)
                    ->add('adminUnit', TextType::class, [
                        'label' => 'admin.massmail.admin.unit',
                        'required' => false,
                        'constraints' => [new Regex(pattern: '/^[A-Za-z0-9]{1,20}$/')],
                    ])
                    ->add('place', IntegerType::class, [
                        'label' => 'admin.massmail.place',
                        'required' => false,
                    ]);
                break;
            case MassmailRights::GROUP:
                $builder->add('group', ChoiceType::class, [
                    'label' => 'adminmassmailenqueuegroup',
                    'choices' => $options['groups'],
                    'placeholder' => '',
                    'attr' => ['class' => 'js-tom-select'],
                    'constraints' => [new NotBlank()],
                ]);
                break;
        }

        $builder->add('enqueue', SubmitType::class, [
            'label' => MassmailRights::TERMS_OF_USE === $options['recipients'] ? 'adminmassmailenqueuesubmittermsofuse' : 'adminmassmailenqueue',
            'attr' => ['class' => 'btn-primary'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('recipients');
        $resolver->setAllowedValues('recipients', [
            MassmailRights::MEMBERS, MassmailRights::LOCATION, MassmailRights::GROUP, MassmailRights::TERMS_OF_USE,
        ]);
        $resolver->setDefaults([
            'countries' => [],
            'groups' => [],
        ]);
    }
}
