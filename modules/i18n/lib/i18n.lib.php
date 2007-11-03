<?php
class MOD_i18n
{
    private $_fallbackLang = 'en';
    private $_lang;
    private $_envVars = array();

    protected $fallbackLangFile;
    protected $langFile;
    
	public function __construct($file)
    {
        $this->_lang = PVars::get()->lang;
        $fallbackLangFile = TEXT_DIR.$this->_fallbackLang.'/'.$file;
        $langFile = TEXT_DIR.$this->_lang.'/'.$file;
        if (!file_exists($fallbackLangFile) || !is_readable($fallbackLangFile))
            throw new PException('Fallback language file not found!');
        if (!file_exists($langFile) || !is_readable($langFile))
            $langFile = $fallbackLangFile;
        $this->langFile = $langFile;
        $this->fallbackLangFile = $fallbackLangFile;
    }
    
    public function getText($name)
    {
    	if (count($this->_envVars) > 0) {
    		foreach ($this->_envVars as $v=>$val) {
    			$$v = $val;
    		}
    	}
        $resultArray = array();
        require $this->langFile;
        if (!isset($$name) || !is_array($$name))
            return array();
        $resultArray = $$name;
        if ($this->_lang == $this->_fallbackLang)
            return $resultArray;
        require $this->fallbackLangFile;
        if (!isset($$name) || !is_array($$name))
            throw new PException('Error in fallback language file!');
        if (count($resultArray) == count($$name))
            return $resultArray;
        $fallbackArray = $$name;
        $keys = array_keys($$name);
        foreach ($keys as $key) {
        	if (!array_key_exists($key, $resultArray))
                $resultArray[$key] = $fallbackArray[$key];
        }
        return $resultArray;
    }

    public function setEnvVar($name, $value)
    {
    	$this->_envVars[$name] = $value;
    }
}
?>