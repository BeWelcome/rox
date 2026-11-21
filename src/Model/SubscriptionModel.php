<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\NewsletterRecipient;
use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function unsubscribeNewsletter(string $username, string $unsubscribeKey): bool
    {
        $entityManager = $this->entityManager;
        $broadcastRepository = $entityManager->getRepository(NewsletterRecipient::class);
        $memberRepository = $entityManager->getRepository(Member::class);
        /** @var NewsletterRecipient $broadcast */
        $broadcast = $broadcastRepository->findOneBy(['unsubscribeKey' => $unsubscribeKey]);
        if (null === $broadcast) {
            return false;
        }

        /** @var Member $member */
        $member = $memberRepository->find($broadcast->getReceiver());
        if ($username !== $member->getUsername()) {
            return false;
        }

        switch ($broadcast->getNewsletter()->getType()) {
            case Newsletter::SPECIFIC_NEWSLETTER:
                $preference = Preference::LOCAL_EVENT_NOTIFICATIONS;
                break;
            case Newsletter::REGULAR_NEWSLETTER:
                $preference = Preference::NEWSLETTERS_VIA_EMAIL;
                break;
            default:
                return false;
        }

        $preferenceRepository = $entityManager->getRepository(Preference::class);

        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => $preference]);
        $memberPreference = $member->getMemberPreference($preference);
        $memberPreference->setValue('No');

        $entityManager->persist($memberPreference);
        $entityManager->flush();

        return true;
    }
}
