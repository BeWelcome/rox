<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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
class GroupSendMessagePage extends GroupsBasePage
{
    protected function getSubmenuActiveItem() {
        return 'message';
    }
    
    public function column_col3()
    {
        $layoutkit = $this->layoutkit;

        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('GroupsController', 'groupSendMessage');
        
        if ($redirected = $formkit->mem_from_redirect)
        {
            $post = ((is_array($redirected->post)) ? $redirected->post : array());
            $status = $redirected->status;
        }

        $router = new RequestRouter();
        $status_message = ((isset($status)) ? "<p>" . (($status) ? $this->words->get('GroupMessageSendSuccess'): $this->words->get('GroupMessageSendFailure') ) . "</p>": '');

        echo <<<HTML
        {$status_message}
<form action='{$router->url('group_send_message', array('group_id' => $this->group->id))}' method='post'>
    {$callback_tag}
    <label for='group_subject'>{$this->words->get('GroupMessageSubject')}</label><input type='text' name='subject' id='group_subject'/>
    <label for='group_message'>{$this->words->get('GroupMessageBody')}</label><textarea name='message' id='group_message'></textarea>
    <input type='submit' value='{$this->words->getSilent('Send')}'/>
</form>
HTML;

    }
}

?>
