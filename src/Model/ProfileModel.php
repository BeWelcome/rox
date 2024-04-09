<?php

namespace App\Model;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Form\ProfileStatusFormType;
use App\Service\Mailer;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mailer\MailerInterface;

class ProfileModel
{
    private FormFactoryInterface $formFactory;
    private EntityManagerInterface $entityManager;
    private Mailer $mailer;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        Mailer $mailer)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
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

    public function retireProfile(Member $member, array $data): bool
    {
        $feedback = $data['feedback'];
        if (!empty($feedback)) {
            $this->mailer->sendProfileDeletionFeedback($member, $feedback);
        }

        $member->setStatus(MemberStatusType::ASKED_TO_LEAVE);

        $dataRetention = $data['data_retention'] ?? false;
        if ($dataRetention) {
            $retentionDate = (new Carbon())->subYears(1);
            $member->setLastLogin($retentionDate);
        }

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return true;
    }
}
