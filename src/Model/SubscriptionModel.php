<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Preference;
use App\Utilities\ManagerTrait;

class SubscriptionModel
{
    use ManagerTrait;

    public function unsubscribeNewsletter(Member $member, Newsletter $newsletter): void
    {
        switch ($newsletter->getType()) {
            case 'Specific':
                $preference = Preference::LOCAL_EVENT_NOTIFICATIONS;
                break;
            case 'Normal':
                $preference = Preference::NEWSLETTERS_VIA_EMAIL;
                break;
            default:
                throw new \InvalidArgumentException('Wrong newsletter type');
        }
        $entityManager = $this->getManager();

        $preferenceRepository = $entityManager->getRepository(Preference::class);

        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => $preference]);
        $memberPreference = $member->getMemberPreference($preference);
        $memberPreference->setValue('No');

        $entityManager->persist($memberPreference);
        $entityManager->flush();
    }
}
