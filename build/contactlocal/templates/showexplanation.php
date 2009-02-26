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

$words = $this->getWords();

if (!empty($errormessage)) {
    ?>
    <p class="error"><?=$errormessage;?></p>;
    <?
}

?>
<p class="note">
This pages allows local volunteers to create new messages for the members in the area they are volunteering.<br />
Here a local volunteer can create a messages, select the area which will receive it and create several version of this messages in the various languages he speaks.</br >
Usually the messages created by the local volunteers must be approved by the local volunteers coordinators to prevent misunderstood and/or spamming (yes mass mailing is a science).<br />
They are also limited quota on how many messages per month a local volunteer can send.</br >
They are more details about this function in the wiki
</p>

