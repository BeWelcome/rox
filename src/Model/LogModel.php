<?php

namespace App\Model;

use App\Entity\Member;
use App\Pagerfanta\LogAdapter;
use App\Utilities\ManagerTrait;
use Pagerfanta\Pagerfanta;

class LogModel
{
    use ManagerTrait;

    /**
     * Returns a Pagerfanta object that contains the currently selected logs.
     *
     * @param $member
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getFilteredLogs(array $types, $member, $page, $limit)
    {
        $adapter = new LogAdapter($this->getManager(), $types, $member);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($limit);
        $pagerFanta->setCurrentPage($page);

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
