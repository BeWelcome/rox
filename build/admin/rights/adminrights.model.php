<?php

error_log('Model');
class AdminRightsModel extends RoxModelBase {

    /**
     * get all rights for a member
     *
     * @access public
     * @return list of rights
     */
    public function getRightsForMember(Member $member) {
        $rights = $this->findBySQLMany($query);
        return $rights;
    }
}