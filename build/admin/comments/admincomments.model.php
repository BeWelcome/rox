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
    public function getComments($type)
    {
        echo "HUHU";
        if($type === "All")
            return $this->createEntity('Comment')->findByWhereMany("AdminAction NOT IN ('NothingNeeded', 'Checked')");
        elseif($type === "Abusive")
            return $this->createEntity('Comment')->findByWhereMany("AdminAction NOT IN ('NothingNeeded', 'Checked')");
        else // default: "Negative"
            return $this->createEntity('Comment')->findByWhereMany("AdminAction NOT IN ('NothingNeeded', 'Checked')");
    }
    
    /**
     * TODO: this a duplicate of a method in members.model.php: consolidate the two!
     *
     * @param type $id
     * @return type
     */
    public function getComment($id)
    {
        $result = $this->createEntity('Comment')->findById($id);
        return $result;
    }
    
    /*
    public function deleteComment($comment)
    {
        if (!is_object($comment) || !$comment->isLoaded())
        {
            return false;
        }
        return $comment->deleteComment();
    }
     */
    
    public function deleteComment($id)
    {
        return $this->createEntity('Comment')->findById($id)->delete();
    }
    
    public function toggleHideComment($id)
    {
        // FIXME: faked dummy
        $visibility = true;
        return $this->createEntity('Comment')->findById($id)->update('visible', $visibility);
    }
    
    public function checkedComment($id)
    {
        // FIXME: faked dummy
        return $this->createEntity('Comment')->findById($id)->update('checked', 'true');
    }
}