<?php

/**
 * Class AdminSubscriptionsModel
 */
class AdminSubscriptionsModel extends RoxModelBase {

    public function checkManageVarsOk($vars) {
        $errors = [];
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
        $memberId = (int) $member->id;
        $enabled = $enable ? 1 : 0;
        // the subscriptions stay; only their notifications are switched (as forums.model.php does)
        $this->dao->query("
            UPDATE
                members_threads_subscribed
            SET
                notificationsEnabled = " . $enabled . "
            WHERE
                IdSubscriber = " . $memberId);
        $this->dao->query("
            UPDATE
                group_membership
            SET
                notifications_enabled = " . $enabled . "
            WHERE
                member_id = " . $memberId . "
                AND accept_mass_mail = 1");
    }
}