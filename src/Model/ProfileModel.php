<?php

namespace App\Model;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Form\ProfileStatusFormType;
use App\Service\Mailer;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ProfileModel
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly Mailer $mailer,
    ) {
    }

    public function getStatusForm(Member $loggedInMember, Member $member): ?FormInterface
    {
        $statusForm = null;
        $admin = $this->security->isGranted(Member::ROLE_ADMIN_ADMIN, $member)
            || $this->security->isGranted(Member::ROLE_ADMIN_SAFETYTEAM, $member)
            || $this->security->isGranted(Member::ROLE_ADMIN_PROFILE, $member);

        if ($admin) {
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
            $retentionDate = new Carbon()->subYears(1);
            $member->setLastActive($retentionDate);
        }

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return true;
    }
}
