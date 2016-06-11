<?php
/**
 * blog model
 *
 * @package blog
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id:blog.model.php 201 2007-02-11 14:07:56Z marco $
 */
class Shouts extends PAppModel 
{
    private $_dao;
    private $_namespace;

    /*
     * Blogentry flags
     * 
     * binary
     */
    /**
     * private
     */
    const FLAG_VIEW_PRIVATE   = 1;
    /**
     * protected
     */
    const FLAG_VIEW_PROTECTED = 2;
    const FLAG_STICKY         = 8; 
    /**
     * anyone may comment
     */
    const FLAG_COMMENT_ALL    = 16;

    public function __construct() 
    {
        parent::__construct();
    }

    /*
         get comments
        */
    public function getShouts($table,$table_id = 0) {
    	$query = 
        "
SELECT
    c.id                          AS shout_id,
    c.member_id_foreign           AS IdMember,
    u.username                    AS username,
    u.Status                      AS MemberStatus,
    UNIX_TIMESTAMP(c.created)     AS unix_created,
    c.title,
    c.created,
    c.text
FROM
    shouts c
    JOIN members u ON c.member_id_foreign = u.id
    AND u.Status IN ('Active')
WHERE 
    c.table = '".$table."'
    AND c.table_id = '".(int)$table_id."'
ORDER BY 
    c.created DESC
        "
        ;
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        $s_links = array();
        foreach ($s as $s1) {
            $s1->text = $this->cleanupText($s1->text);
            array_push($s_links, $s1);
        }
        return $s_links;
    }

    public function deleteShouts($table,$table_id = 0) {
    	$query = '
DELETE FROM `shouts`
WHERE `table` = \''.$table.'\'
AND `table_id` = '.(int)$table_id.'
        ';
        return $this->dao->exec($query);
    }
    
    /**
     * Processing creation of a comment
     *
     * This is a POST callback function.
     *
     * Sets following errors in POST vars:
     * title        - invalid(empty) title.
     * textlen      - too short or long text.
     * inserror     - db error while inserting.
     */
    public function shoutProcess($table = false,$table_id = false) {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            if (!$this->_session->get('IdMember'))
                return false;
            $vars =& PPostHandler::getVars();
            $request = PRequest::get()->request;
            if (!$table)
                $table = $vars['table'];
            if (!$table_id)
                $table_id = $vars['table_id'];
            // validate
            if (!isset($vars['ctxt']) || strlen($vars['ctxt']) == 0 || strlen($vars['ctxt']) > 5000) {
                $vars['errors'] = array('textlen');
                return false;
            }

            $shoutId = $this->dao->nextId('shouts');
            $query = '
INSERT INTO `shouts`
SET
    `id`='.$shoutId.',
    `table`=\''.$table.'\',
    `table_id`=\''.$table_id.'\',
    `member_id_foreign`='.$this->_session->get('IdMember').',
    `title`=\''.(isset($vars['ctit'])?$this->dao->escape($vars['ctit']):'').'\',
    `text`=\''.$this->dao->escape($vars['ctxt']).'\',
    `created`=NOW()';
            $s = $this->dao->query($query);
            if (!$s) {
                $vars['errors'] = array('inserror');
                return false;
            }
            PPostHandler::clearVars();
            return PVars::getObj('env')->baseuri.implode('/', $request).'#c'.$shoutId;
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }
    
        private function makeClickableLinks($text) 
        {    
            $text = preg_replace('/(((f|ht){1}tp:\/\/)[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/i',
                '<a href="\\1">\\1</a>', $text);
            $text = preg_replace('/([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/i',
                '\\1<a href="http://\\2">\\2</a>', $text);
            $text = preg_replace('/([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})/',
                '<a href="mailto:\\1">\\1</a>', $text);
            return $text;
        }

/*
* cleanupText
*
*
*
*/
    private function cleanupText($txt) {
		if (strpos($txt,"href=")===false)  { // We will only try to make clickable links if there is not yet a href= (ie already present clickable link) in the text
			$txt = $this->makeClickableLinks($txt);
		}
        $purifier = MOD_htmlpure::get()->getPurifier();
        $txt = $purifier->purify($txt);
        return $txt;
    } // end of cleanupText

}
?>
