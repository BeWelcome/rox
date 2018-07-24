<?php

namespace AppBundle\Model;

use AppBundle\Pagerfanta\LogAdapter;
use Pagerfanta\Pagerfanta;

class LogModel extends BaseModel
{
    /**
     * Returns a Pagerfanta object that contains the currently selected logs.
     *
     * @param array $types
     * @param $member
     * @param int $page
     * @param int $limit
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getFilteredLogs(array $types, $member, $page, $limit)
    {
        $adapter = new LogAdapter($this->em, $types, $member);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($limit);
        $pagerFanta->setCurrentPage($page);

        return $pagerFanta;
    }

    public function getLogTypes()
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
            'DataRetention',
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
            'UpdateFaq',
            'UpdatePreference',
            'updateprofile',
            'updatemandatory',
            'UpdatingBoard',
            'uploadphoto',
            'VerifyMember',
        ];
        $logTypes = array_combine($types, $types);

        return $logTypes;
    }
}
