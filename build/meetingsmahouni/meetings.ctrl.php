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
 * meetings controller
 *
 * @package meetings
 * @author BeVolunteer - Toni (mahouni) (based on lemon-head`s groups application)
 */


/**
 * This controller is called when the request is 'meetings/...'
 */
class MeetingsController extends PAppController
{
    public function index()
    {
        $request = PRequest::get()->request;
        $model = new MeetingsModel();
        if (!isset($request[1])) {
            $page = new MeetingsOverviewPage();
        } else if (is_numeric(
            $meeting_id = array_shift(explode('-', $request[1]))
        )) {
            // by default, the $request[1] is the meeting id + name
            if (!$meeting = $model->findMeeting($meeting_id)) {
                // meeting does not exist. redirect to meetings overview page or search
                $this->_redirect('meetings');
            } else {
                $model->setMeetingVisit($meeting_id);
                $page = $this->_getMeetingPage($meeting, $request);       
            }

        } else switch ($request[1]) {
            case 'search':
                $page = new MeetingsSearchPage();
                $page->setSearchQuery($search_query);
                break;
            case 'new':
                $page = new MeetingsCreationPage();
                break;
            default:
                $this->_redirect('meetings');
        }
        $page->setModel($model);
        $page->render();
    }

    private function _getMeetingPage($meeting, $request)
    {
        if (!isset($request[2])) {
            $page = new MeetingStartPage();
        } else switch ($request[2]) {
                // which meeting subpage is requested?
            case 'join':
                if (!isset($request[3])) {
                    $page = new MeetingJoinPage();
                } else switch($request[3]) {
                    case 'yes':
                        $this->joinMeeting($meeting);
                        $page = new MeetingStartPage();
                        // TODO: set a message for 'meeting not joined'
                        break;
                    case 'no':
                        $page = new MeetingStartPage();
                        // TODO: set a message for 'meeting not joined'
                    default:
                        $this->_redirect('meetings/'.$request[1].'/join');
                }
                break;
            case 'leave':
                if (!isset($request[3])) {
                    $page = new MeetingLeavePage();
                } else switch($request[3]) {
                    case 'yes':
                        $this->leaveMeeting($meeting);
                        $page = new MeetingStartPage();
                        // TODO: set a message for 'meeting not joined'
                        break;
                    case 'no':
                        $page = new MeetingStartPage();
                        // TODO: set a message for 'meeting not joined'
                    default:
                        $this->_redirect('meetings/'.$request[1].'/leave');
                }
                break;
            case 'members':
                $page = new MeetingMembersPage();
                break;
            default:
                $page = new MeetingStartPage();
        }
        $page->setMeeting($meeting);
        return $page;
    }
    
    private function _redirect($rel_url)
    {
        /*
        echo PVars::getObj('env')->baseuri.'<br>';
        echo PVars::getObj('env')->baseuri.implode('/', PRequest::get()->request).'<br>';
        echo PVars::getObj('env')->baseuri.$rel_url;
        */
        header('Location: '.PVars::getObj('env')->baseuri.$rel_url);
        PPHP::PExit();
    }
}


?>
