<?php

namespace App\Form;

use App\Doctrine\MemberStatusType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileStatusFormType extends AbstractType
{
    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * Parameter $options not used but signature is given by symfony.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $memberStatusType = new MemberStatusType();
        $statuses = $memberStatusType->getStatuses();

        $builder
            ->setAction('/members/status/set')
            ->setMethod('POST')
            ->add('status', Select2Type::class, [
                'label' => 'profile.status',
                'choices' => $statuses,
            ])
            ->add('member', HiddenType::class)
        ;
    }
}
