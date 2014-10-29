<?php

/**
 * Class AdminCommentsModel
 */
class AdminCommentsModel extends RoxModelBase {

     /**
     * @return array of all comments but those that are checked or don't need anything;
     * array of negative comments; or array of abusive comments.
     */
    public function getSubset($subset)
    {
        $entity = $this->createEntity('Comment');
        $entity->sql_order = "updated DESC";
        if($subset === "all")
            // This filter is unlike production, but I found it in the given code for /build and it makes 
            // sense. I therefore keep it as an improvement.
            return $entity->findByWhereMany(
                    "AdminAction NOT IN ('NothingNeeded', 'Checked')");
        elseif($subset === "abusive")
            return $entity->findByWhereMany(
                    "AdminAction = 'AdminAbuserMustCheck'");
        elseif($subset === "negative")
            return $entity->findByWhereMany("AdminAction = 'AdminCommentMustCheck'");
        return null;
    }
    
    /**
     * 
     * @param type $id of author
     * @return array of comments written by user with given ID
     */
    public function getFrom($id)
    {
        if(!is_int($id))
        {
            return null;
        }
        $entity = $this->createEntity('Comment');
        $entity->sql_order = "updated DESC";
        return $entity->findByWhereMany("IdFromMember = ".$id);
    }
    
    /**
     * 
     * @param type $id of user that is subject of a comment
     * @return array of comments written about user with given ID
     */
    public function getTo($id)
    {
        if(!is_int($id))
        {
            return null;
        }
        $entity = $this->createEntity('Comment');
        $entity->sql_order = "updated DESC";
        return $entity->findByWhereMany("IdToMember = ".$id);
    }
    
    /**
     * TODO: this a duplicate of a method in members.model.php: consolidate the two!
     *
     * @param type $id of a comment
     * @return array containing a single comment
     */
    public function getSingle($id)
    {
        $result = $this->createEntity('Comment')->findById($id);
        $a = array();
        $a[] = $result;
        return $a;
    }
    
    // TODO: this is a dummy
    // @see MembersModel.checkCommentForm()
    public function checkUpdate(&$vars)
    {
        $errors = array();
//        if (!isset($vars['TextFree'])) {
//            $errors[] = 'Comment_NoCommentLengthSelected';
//        }
        return $errors;
    }
    
    /**
     * 
     * @param type $c the comment to update
     * @param string $vars the array used to update the attributes of the comment
     * @return the updated comment
     */
    public function update($c, &$vars)
    {
        $this->dao->escape($vars['TextWhere']);
        $this->dao->escape($vars['TextFree']);
        $this->dao->escape($vars['Quality']);

        $syshcvol = PVars::getObj('syshcvol');
        
        $proximity = "";
        foreach($syshcvol->LenghtComments as $elem)
        {
            if(!array_key_exists($elem, $vars))
            {
                $vars[$elem] = "off";
            }
            $this->dao->escape($vars[$elem]);
            $proximity .= ($vars[$elem]=='on' ? ($elem.',') : '');
        }
        
        if(strlen($proximity)>0)
            $proximity = substr($proximity, 0, strlen($proximity)-1);
        
        $c->TextWhere = $vars['TextWhere'];
        $c->TextFree = $vars['TextFree'];
        $c->Quality = $vars['Quality'];
        $c->Lenght = $proximity;
        
        return $c->update();
    }

    /**
     * 
     * @param type $id of the comment that is to be deleted
     * @return type
     */
    public function delete($id)
    {
        return $this->createEntity('Comment')->findById($id)->delete();
    }
    
    /**
     * 
     * @param type $id of the comment that is to be hidden/shown
     * @return type
     */
    public function toggleHide($id)
    {
        $c = $this->createEntity('Comment')->findById($id);
        $c->DisplayInPublic = ($c->DisplayInPublic==0 ? 1 : 0);
        return $c->update();
    }
    
    /**
     * 
     * @param type $id of the comment that is to be allowed/forbidden for updates
     * @return type
     */
    public function toggleAllowEdit($id)
    {
        $c = $this->createEntity('Comment')->findById($id);
        $c->AllowEdit = ($c->AllowEdit==0 ? 1 : 0);
        return $c->update();
    }
    
    /**
     * 
     * @param type $id of the comment that is to be marked as checked
     * @return type
     */
    public function markChecked($id)
    {
        return $this->_setAdminAction($id, "Checked");
    }
    
    /**
     * 
     * @param type $id of the comment that is to be marked as must-check
     * @return type
     */
    public function markAdminAbuserMustCheck($id)
    {
        return $this->_setAdminAction($id, "AdminAbuserMustCheck");
    }
    
    /**
     * 
     * @param type $id of the comment that is to be marked as must-check
     * @return type
     */
    public function markAdminCommentMustCheck($id)
    {
        return $this->_setAdminAction($id, "AdminCommentMustCheck");
    }
    
    /**
     * Helper function to update a specific attribute of a comment.
     * 
     * @param type $id
     * @param type $value
     * @return type
     */
    private function _setAdminAction($id, $value)
    {
        $c = $this->createEntity('Comment')->findById($id);
        $c->AdminAction = $value;
        return $c->update();
    }
}