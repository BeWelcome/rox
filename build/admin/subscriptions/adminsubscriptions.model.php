<?php

/**
 * Class AdminSubscriptionsModel
 */
class AdminSubscriptionsModel extends RoxModelBase {

    public function checkManageVarsOk($vars) {
        $errors = array();
        if (empty($vars['username'])) {
            $errors[] = 'AdminManageSubscriptionsNameEmpty';
        } else {
            $temp = new Member();
            $member = $temp->findByUsername($vars['username']);
            if (!$member) {
                $errors[] = 'AdminManageSubscriptionsWrongName';
            }
        }
        return $errors;
    }

    public function manageSubscriptions($vars, $enable) {
        $temp = new Member();
        $member = $temp->findByUsername($vars['username']);
        $memberId = $member->id;
        // update subscription (keep old assignments through negating if disabling)
        // members_tags_subscribed
        // members_threads_subscribed
        if ($enable) {
            $newSubscriberId = $memberId;
            $oldSubscriberId = (-1) * $memberId;
        } else {
            $newSubscriberId = (-1) * $memberId;
            $oldSubscriberId = $memberId;
        }
        $query = "
            UPDATE
                members_tags_subscribed
            SET
                IdSubscriber = " . $newSubscriberId . "
            WHERE
                IdSubscriber = " . $oldSubscriberId;
        $this->dao->query($query);
        $query ="
            UPDATE
                members_threads_subscribed
            SET
                IdSubscriber = " . $newSubscriberId . "
            WHERE
                IdSubscriber = " . $oldSubscriberId;
        $this->dao->query($query);
        $query = "
            UPDATE
                membersgroups
            SET
                IdMember = " . $newSubscriberId . "
            WHERE
                IdMember = " . $oldSubscriberId . "
                AND IacceptMassMailFromThisGroup = 'Yes'
        ";
        $this->dao->query($query);
    }
}