<?php

namespace App\Model;

use App\Entity\Activity;
use App\Entity\Member;
use App\Entity\Message;
use App\Repository\ActivityRepository;
use Doctrine\ORM\Query;
use Exception;

class LandingModel extends BaseModel
{
    /**
     * Generates messages for display on home page.
     *
     * Returns either all messages or only unread ones depending on checkbox state
     *
     * Format: 'title': "Message title #1",
     *   'id': 12345,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',
     *   'read': true
     *
     * @param Member $member
     * @param $all
     * @param $unread
     * @param int|bool $limit
     *
     * @return array
     */
    public function getMessages(Member $member, $unread, $limit = 0)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('m')
            ->from('AppBundle:Message', 'm')
            ->where('m.receiver = :member')
            ->setParameter('member', $member);
        if ($unread) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('m.whenfirstread', "'0000-00-00 00:00.00'"),
                        $qb->expr()->isNull('m.whenfirstread')
                    )
                );
        }

        if (0 !== $limit) {
            $qb->setMaxResults($limit);
        }

        // throw new Exception($qb->getDQL());

        return $qb
            ->orderBy('m.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Generates notifications for display on home page
     * Format: 'title': "Message title #1",
     *   'text': Depending on type of notification,
     *   'link': Depending on type of notification,
     *   'user': 'Member-102',
     *   'time': '10 minutes ago',.
     *
     * @param Member   $member
     * @param int|bool $limit
     *
     * @return array
     */
    public function getNotifications(Member $member, $limit = 0)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('n')
            ->from('AppBundle:Notification', 'n')
            ->where('n.member = :member')
            ->setParameter('member', $member)
            ->setMaxResults($limit);

        return $queryBuilder
            ->orderBy('n.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Generates threads for display on home page.
     *
     * Depends on checkboxes shown above the display
     *
     * @param Member   $member
     * @param bool     $groups
     * @param bool     $forum
     * @param bool     $following
     * @param int|bool $limit
     *
     * @return array
     */
    public function getThreads(Member $member, $groups, $forum, $following, $limit = 0)
    {
        if (0 === $groups + $forum + $following) {
            // Member decided not to show anything
            return [];
        }

        $em = $this->em;

        // There seems to be an issue with some threads/posts missing their author.
        // To get around that, this task is split it two parts: first do the search
        // query we want using inner join on all required dependent tables, then second,
        // use the IDs from that result set to do a findMany using the ORM.

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder
            ->select('ft')
            ->from('AppBundle:ForumThread', 'ft')
            ->where("ft.threadDeleted = 'NotDeleted'")
            ->orderBy('ft.createdAt', 'desc')
        ;

        $groupIds = [];
        if ($groups) {
            $groupIds = array_map(function ($group) {
                return $group->getId();
            }, $member->getGroups());
            $queryBuilder
                ->andWhere('ft.group IN (:groups)')
                ->setParameter('groups', $groupIds);
        } else {
            $queryBuilder
                ->andWhere('ft.group = 0');
        }
        if ($following) {
        }

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        $query = $queryBuilder->getQuery();

        $posts = $query->getResult();

        return $posts;
    }

    /**
     * Generates activities (near you) for display on home page.
     *
     * @param Member $member
     *
     * @return array
     */
    public function getActivities(Member $member)
    {
        /** @var ActivityRepository $repository */
        $repository = $this->em->getRepository(Activity::class);
        $activities = $repository->findUpcomingAroundLocation($member->getCity());

        return $activities;
    }

    public function getMemberDetails()
    {
        /*        $loggedInMember = $this->getLoggedInMember();
        $location = Capsule::table('geonames')->where('geonameId', $loggedInMember->IdCity)->first(['name']);
        return ['member' =>
            [
                'location' => $location->name,
                'hosting' => $loggedInMember->Accomodation
            ]
        ];
*/
    }

    public function getDonationCampaignDetails()
    {
    }

    /**
     * @param Member $member
     *
     * @return array|bool
     */
    public function getTravellersInAreaOfMember(Member $member)
    {
        $member;
        /*        $travellers = false;
                $trip = new Trip();
                $trips = $trip->findInMemberAreaNextThreeMonths( $member );
                if($trips) {
                    foreach($trips as $t) {
                        $traveller = new \stdClass;
                        $traveller->Username = $t->createdBy->Username;
                        $traveller->arrives = $t->subtrips[0]->arrival;
                        $traveller->leaves = $t->subtrips[0]->departure ? $t->subtrips[0]->departure : $t->subtrips[0]->arrival;
                        $traveller->livesIn = $t->createdBy->city;
                        $travellers[] = $traveller;
                    }
                }
                return $travellers;
        */
    }

    /**
     * @param Member $member
     * @param $accommodation
     *
     * @return Member
     */
    public function updateMemberAccommodation(Member $member, $accommodation)
    {
        $member->setAccommodation($accommodation);
        $this->em->persist($member);
        $this->em->flush($member);

        return $member;
    }
}
