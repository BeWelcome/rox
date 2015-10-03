<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Contains the exception handler
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: exception.lib.php 108 2006-07-08 17:03:54Z kang $
 */
/**
 * Exception handler
 * 
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: exception.lib.php 108 2006-07-08 17:03:54Z kang $
 */
class PException2 extends Exception  {
    private $_info = array();
    
    public function __construct($msg = NULL, $code = 0) {
        parent::__construct($msg, $code);
    }
    
    public function addInfo($msg) {
        $this->_info[] = $msg;
    }
    
    
    public function getInfo()
    {
        return $this->_info;
    }
    
    
    public function __toString() {
        $eStr = '<?xml version="1.0" encoding="utf-8"?>';
        $eStr.= '<exception>';
        $eStr.= '<code>'.$this->getCode().'</code>';
        $eStr.= '<message>'.htmlentities($this->getMessage(), ENT_COMPAT, 'utf-8').'</message>';
        $eStr.= '<file>'.$this->getFile().'</file>';
        $eStr.= '<line>'.$this->getLine().'</line>';
        if (count($this->_info) > 0) {
            foreach ($this->_info as $inf) {
                $eStr.='<info>'.htmlentities($inf, ENT_COMPAT, 'utf-8').'</info>';
            }
        }
        $eStr.= '<trace>';
        $eStr.= $this->__traceXML();
        $eStr.= '</trace>';
        $eStr.= '</exception>';
        $d = @DOMDocument::loadXML($eStr);
        if (!$d) {
            $eStr = "Non-XML Error:\r\n".$eStr;
            return $eStr;
        }
        $d->formatOutput = true;
        return $d->saveXML();
    }
    
    private function __traceXML() {
        $trace = $this->getTrace();
        $tStr = '';
        foreach ($trace as $i=>$t) {
            $tStr.='<event stackno="'.$i.'">';
            if (isset($t['file'])) {
                $tStr.='<file>'.$t['file'].'</file>';
            }
            if (isset($t['line'])) {
                $tStr.='<line>'.$t['line'].'</line>';
            }
            if (isset($t['class'])) {
                $tStr.='<class type="'.(isset($t['type']) ? $t['type'] : 'functioncall').'">'.$t['class'].'</class>';
            }
            if (isset($t['function'])) {
                $tStr.='<function>'.$t['function'].'</function>';
            }
            if (isset($t['args']) && is_array($t['args'])) {
                $tStr.='<args>';
                foreach ($t['args'] as $arg) {
                    $tStr.='<arg>'.(is_object($arg) ? get_class($arg) : $arg).'</arg>';
                }
                $tStr.='</args>';
            }
            $tStr.='</event>';
        }
        return $tStr;
    }
}
?>