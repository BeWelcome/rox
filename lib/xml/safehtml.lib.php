<?php
/**
 * contains safeHTML lib
 * 
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: xml_data.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * safeHTML lib
 * 
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: xml_data.lib.php 68 2006-06-23 12:10:27Z kang $
 */
class PSafeHTML {
    private $_doc;
    private $_allow = array();
    private $_allowAttributes = array();
    
    public function __construct(DOMDocument $doc) {
        $doc->x = new DOMXPath($doc);
        $this->_doc = $doc;
    }
    
    public function allow($tagName) {
        $this->_allow[] = $tagName;
    }
    
    public function allowAttribute($attribName) {
        $this->_allowAttributes[] = $attribName;
    }
    
    public function clean() {
        // remove tags
        $query = '//*';
        $allow = array();
        foreach ($this->_allow as $a) {
            $allow[] = 'name()!="'.$a.'"';
        }
        if (count($allow) > 0) {
            $query .= '['.implode(' and ', $allow).']';
        }
        $unwantedTags = $this->_doc->x->query($query);
        foreach ($unwantedTags as $tag) {
            $tag->parentNode->removeChild($tag);
        }

        // remove attributes
        $allow = array();
        foreach ($this->_allowAttributes as $attrib) {
        	$allow[] = 'not(@'.$attrib.')';
        }
        $query = '//*[@*'.(count($allow) > 0 ? ' and '.implode(' and ', $allow): '').']';
        $unwantedTags = $this->_doc->x->query($query);
        foreach ($unwantedTags as $tag) {
        	$attribs = $this->_doc->x->query('@*', $tag);
            foreach ($attribs as $attrib) {
            	$attrib->ownerElement->removeAttribute($attrib->name);
            }
        }
        return true;
    }
    
    public function getDoc() {
        return $this->_doc;
    }
}
?>