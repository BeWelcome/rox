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
// get current request
$request = PRequest::get()->request;
?>
<h2>
You can create your own room, it will be a private room where you will be able to invite people to tal
</h2>
<form name="createroom" action="chat/docreateroom"  id="idcreateroom" method="post">
<input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
<input type="hidden" name="<?=$callbackId ?>"  value="1"/>
Room Short Title :<input type="text" name="RoomTitle"><br /> 
You can add here a description explaining why you created this room : <br /><textarea name="RoomDescription" cols="80" rows="3"></textarea> <br />
<input type="submit" name="submit" value="create">
</form>
