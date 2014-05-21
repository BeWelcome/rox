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
    public function get($type)
    {
        if($type === "All")
            return $this->createEntity('Comment')->findByWhereMany(
                    "AdminAction NOT IN ('NothingNeeded', 'Checked')");
        elseif($type === "Abusive")
            return $this->createEntity('Comment')->findByWhereMany(
                    "AdminAction NOT IN ('NothingNeeded', 'Checked')");
        else // default: "Negative"
            return $this->createEntity('Comment')->findByWhereMany(
                    "AdminAction NOT IN ('NothingNeeded', 'Checked')");
    }
    
    /**
     * TODO: this a duplicate of a method in members.model.php: consolidate the two!
     *
     * @param type $id
     * @return type
     */
    public function getSingle($id)
    {
        $result = $this->createEntity('Comment')->findById($id);
        return $result;
    }
    
    public function checkUpdate($vars) {
        
    }
    
    // TODO: dummy
    public function update($vars) {
        // $this->dao->escape($vars['level'])
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
        $this->_setAdminAction($id, "Checked");
    }
    
    public function markAdminAbuserMustCheck($id)
    {
        $this->_setAdminAction($id, "AdminAbuserMustCheck");
    }
    
    public function markAdminCommentMustCheck($id)
    {
        $this->_setAdminAction($id, "AdminCommentMustCheck");
    }
    
    private function _setAdminAction($id, $value)
    {
        $c = $this->createEntity('Comment')->findById($id);
        $c->AdminAction = $value;
        return $c->update();
    }
}