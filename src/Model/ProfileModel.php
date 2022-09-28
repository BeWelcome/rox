<?php

namespace App\Model;

use App\Entity\Member;
use App\Form\ProfileStatusFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ProfileModel
{
    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getStatusForm(Member $loggedInMember, Member $member): ?FormInterface
    {
        $statusForm = null;

        if (in_array(Member::ROLE_ADMIN_SAFETYTEAM, $loggedInMember->getRoles())) {
            $statusFormBuilder = $this->formFactory->createBuilder(ProfileStatusFormType::class, [
                'status' => $member->getStatus(),
                'member' => $member->getId(),
            ]);
            $statusForm = $statusFormBuilder->getForm();
        }

        return $statusForm;
    }
}
