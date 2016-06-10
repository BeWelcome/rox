<?php


class Language extends RoxEntityBase
{
    protected $_table_name = 'languages';


    public function __construct($id = false)
    {
        parent::__construct();
        if ($id !== false)
        {
            $this->findById($id);
        }
    }

    /**
     * sets the currently used language in the session
     *
     * @access public
     * @return bool
     */
    public function setLanguage()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        $this->getSession->set( 'lang', $this->ShortCode )
        $this->getSession->set( 'IdLanguage', $this->id )
        PVars::register('lang', $_SESSION['lang']);
        return true;
    }
}
