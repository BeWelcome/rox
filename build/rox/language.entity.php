<?php


/**
 * @property bool|mixed ShortCode
 * @property bool|mixed id
 */
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
        $_SESSION['lang'] = $this->ShortCode;
        $_SESSION['IdLanguage'] = $this->id;
        PVars::register('lang', $_SESSION['lang']);
        return true;
    }
}
