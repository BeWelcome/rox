<?php

class MemberLanguage extends RoxEntityBase
{

    protected $_table_name = 'memberslanguageslevel';

    public function __construct($id = false)
    {
        parent::__construct();
        if ($id)
        {
            $this->findById($id);
        }
    }

    /**
     * deletes all languages associated with a member
     *
     * @param object $member
     * @access public
     * @return bool
     */
    public function deleteMembersLanguages(Member $member)
    {
        if (!$member->isLoaded())
        {
            return false;
        }
        return (bool) $this->dao->query("DELETE FROM {$this->getTableName()} WHERE IdMember = '{$this->dao->escape($member->getPKValue())}'");
    }

    /**
     * sets a spoken language for a member
     *
     * @param object $member
     * @param object $language
     * @param string $level
     * @access public
     * @return bool
     */
    public function setSpokenLanguage(Member $member, Language $language, $level)
    {
        if (!$member->isLoaded() || !$language->isLoaded() || empty($level))
        {
            return false;
        }
        $this->IdMember = $member->getPKValue();
        $this->IdLanguage = $language->getPKValue();
        $this->Level = $level;
        $this->created = date('Y-m-d H:i:s');
        return $this->insert();
    }
}
