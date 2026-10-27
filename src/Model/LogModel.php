<?php

namespace App\Model;

use App\Entity\Log;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class LogModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * The logs of the given types (all if none) and member (all if none), newest first.
     */
    public function getFilteredLogs(array $types, ?Member $member, int|string $page, int|string $limit): Pagerfanta
    {
        $qb = $this->entityManager->getRepository(Log::class)->createQueryBuilder('l')
            ->addSelect('m')
            ->leftJoin('l.member', 'm')
            ->orderBy('l.created', 'DESC');
        if ([] !== $types) {
            $qb->andWhere('l.type IN (:types)')->setParameter('types', $types);
        }
        if (null !== $member) {
            $qb->andWhere('l.member = :member')->setParameter('member', $member);
        }

        $pagerFanta = new Pagerfanta(new QueryAdapter($qb, fetchJoinCollection: false));
        $pagerFanta->setMaxPerPage((int) $limit);
        $pagerFanta->setCurrentPage((int) $page);

        return $pagerFanta;
    }

    /**
     * @return array|false
     */
    public function getLogTypes(Member $member)
    {
        $types = [
            'accepting',
            'AddressUpdate',
            'AdminComment',
            'Adminflags',
            'admingroup',
            'adminlog',
            'adminmandatory',
            'adminmassmails',
            'adminquery',
            'Adminrights',
            'AdminWord',
            'alarm',
            'BirthdateUpdate',
            'Bug',
            'bw_mail',
            'changepassword',
            'ChangeUsername',
            'chat',
            'checking',
            'Comment',
            'comments',
            'contactmember',
            'cron_task',
            'Data Retention',
            'Debug',
            'delrelation',
            'donation',
            'EmailUpdate',
            'feedback',
            'FlagEvent',
            'Forum',
            'ForumModerator',
            'ForumTag',
            'Gallery',
            'GenderUpdate',
            'Geo',
            'Group',
            'hacking',
            'Image',
            'Invitation',
            'Log',
            'Login',
            'lostpassword',
            'mailbot',
            'MarkSpam',
            'MEMBERUPDATE',
            'Members',
            'message',
            'MyContacts',
            'MyRelations',
            'mytranslators',
            'oldBW',
            'polls',
            'Profile',
            'Profilupdate',
            'Profileupdate',
            'query',
            'readmessage',
            'Request',
            'resendconfirmyourmail',
            'retire',
            'rss',
            'Search',
            'Serach',
            'Signup',
            'sql_query',
            'StopBoringMe',
            'suggestions',
            'SwitchLanguage',
            'Trips',
            'UpdateFaq',
            'UpdatePreference',
            'updateprofile',
            'updatemandatory',
            'UpdatingBoard',
            'uploadphoto',
            'VerifyMember',
        ];
        // Reduce list of types to scope
        $allowedTypes = $member->getScopeForRight(Member::ROLE_ADMIN_LOGS);
        if ('All' !== $allowedTypes[0]) {
            $types = array_intersect($allowedTypes, $types);
        }
        $logTypes = array_combine($types, $types);

        return $logTypes;
    }
}
