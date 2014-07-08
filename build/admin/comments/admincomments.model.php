<?php

/**
 * Class AdminCommentsModel
 */
class AdminCommentsModel extends RoxModelBase {

     /**
     * returns all comments marked bad
     *
     * @access public
     * @return array
     */
    public function getSubset($subset)
    {
        $entity = $this->createEntity('Comment');
        $entity->sql_order = "updated DESC";
        if($subset === "all")
            // TODO: this limitation is not like on production, but I found it
            // in the given code and it makes sense. I therefore assume it's
            // here on purpose, as an improvement, and leave it.
            return $entity->findByWhereMany(
                    "AdminAction NOT IN ('NothingNeeded', 'Checked')");
        elseif($subset === "abusive")
            return $entity->findByWhereMany(
                    "AdminAction = 'AdminAbuserMustCheck'");
        elseif($subset === "negative")
            return $entity->findByWhereMany("AdminAction = 'AdminCommentMustCheck'");
        // TODO: throw error
        return null;
    }
    
    public function getFrom($id)
    {
        $entity = $this->createEntity('Comment');
        $entity->sql_order = "updated DESC";
        return $entity->findByWhereMany("IdFromMember = ".$id);
    }
    
    public function getTo($id)
    {
        $entity = $this->createEntity('Comment');
        $entity->sql_order = "updated DESC";
        return $entity->findByWhereMany("IdToMember = ".$id);
    }
    
//    public function getFromTo($id)
//    {
//        $entity = $this->createEntity('Comment');
//        $cs = $entity->findByWhereMany("id = " . $id);
//        $c = $cs[0];
//        $arr = array("from" => $c->getFromMember()->Username,
//            "to" => $c->getToMember()->Username);
//        return $arr;
//    }
//    
    /**
     * TODO: this a duplicate of a method in members.model.php: consolidate the two!
     *
     * @param type $id
     * @return type
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
        
    public function update($c, &$vars)
    {
        // TODO: all must be escaped in order to avoid SQL injection, right?
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

    public function delete($id)
    {
        return $this->createEntity('Comment')->findById($id)->delete();
    }
    
    public function toggleHide($id)
    {
        $c = $this->createEntity('Comment')->findById($id);
        $c->DisplayInPublic = ($c->DisplayInPublic==0 ? 1 : 0);
        return $c->update();
    }
    
    public function toggleAllowEdit($id)
    {
        $c = $this->createEntity('Comment')->findById($id);
        $c->AllowEdit = ($c->AllowEdit==0 ? 1 : 0);
        return $c->update();
    }
    
    public function markChecked($id)
    {
        return $this->_setAdminAction($id, "Checked");
    }
    
    public function markAdminAbuserMustCheck($id)
    {
        return $this->_setAdminAction($id, "AdminAbuserMustCheck");
    }
    
    public function markAdminCommentMustCheck($id)
    {
        return $this->_setAdminAction($id, "AdminCommentMustCheck");
    }
    
    private function _setAdminAction($id, $value)
    {
        $c = $this->createEntity('Comment')->findById($id);
        $c->AdminAction = $value;
        return $c->update();
    }
}