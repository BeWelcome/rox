<?php
/**
 * Meetings controller
 * 
 * @package meeting
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MeetingsModel extends PAppModel
{
	
	private $meeting_list = 0;
	
    public function __construct()
    {
        parent::__construct();
        
        $this->meeting_list = array();
        
        
        for($i = 0; $i < 5; $i++) {
        	$fromtoday = (rand()%10000)*1000;
        	$meeting = new Meeting($i);
        	$meeting->createMeeting(date('D d.m.Y', time()+$fromtoday), "0.0 0.0", "Meeting ".$i, "MeetingInfo for meeting ".$i);
        	$this->meeting_list[] = $meeting;
        } 
    }
    
    
    public function getMeetings() {
    	return $this->meeting_list;
    }	    
}



/**
 * represents a single meeting
 *
 */
class Meeting extends PAppModel
{
    private $_meeting_id;
    private $_meeting_data = false;
    //date, coordinates, title, info    
    private $_meeting_memberships = 0;
    
    
    public function __construct($meeting_id)
    {
        parent::__construct();
        $this->_meeting_id = $meeting_id;
    }
    

    public function getData()
    {
        if ($this->_meeting_data) {
            // do nothing
        } else {
  			// need DB!	
        }
        return $this->_meeting_data;
    }   

	
	/**
	 * Helps out with test data
	 */
	public function createMeeting($date, $coordinates, $title, $info) {
        $this->_meeting_data['date'] = $date;
        $this->_meeting_data['coordinates'] = $coordinates;
        $this->_meeting_data['title'] = $title;
        $this->_meeting_data['info'] = $info;
	}
} 
?>
