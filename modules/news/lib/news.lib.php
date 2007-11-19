<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
/**
 * Prepare the news content for main page
 * @author JeanYves 
 */
class MOD_news {
    

    /**
     * Singleton instance
     * 
     * @var MOD_visits
     * @access private
     */
    private static $_instance;
    
    public function __construct()
    {
    
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
    }
    
    /**
     * singleton getter
     * 
     * @param void
     * @return PApps
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    /**
     * Retrieve the current number of news. 
     */
    public function NewsCount()
    {

        $query = '
SELECT SQL_CACHE count(*) as cnt  
FROM 	`words`  
WHERE `IdLanguage`=0 and `code` like \'NewsTitle_%\''; 
;
    		$s = $this->dao->query($query);
				if (!$s) {
			 		 throw new PException('Cannot retrieve news count !');
				}

				$row = $s->fetch(PDB::FETCH_OBJ) ;
				return($row->cnt) ;
		} // end of	NewsCount
		
    /**
     * Retrieve the date of a new, based on the date the corresponding english word was created for the news.
     * @wordcode is the code of the words associated to the news we want the date of 		  
     */
    public function NewsDate($wordcode)
    {

        $query = '
SELECT SQL_CACHE created  
FROM 	`words`  
WHERE `IdLanguage`=0 and `code`\'='.$wordcode.'\''; 
;
    		$s = $this->dao->query($query);
				if (!$s) {
			 		 throw new PException('Cannot retrieve news count !');
				}

				$row = $s->fetch(PDB::FETCH_OBJ) ;
				return(date("F j, Y",strtotime($row->created))) ;

		} // end of	NewsDate
		



} // end of MOD_news
?>