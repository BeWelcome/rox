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

?>
<p>
<?=$words->getFormatted("verifymembers_proceedexplanation",$_SESSION["Username"],$m->Username) ?>
</p>
<p>
<form name="proceedtoverify" action="verifymembers/doverifymember"  id="idproceedtoverify" method="post">
    
    <!-- The following will disable the nasty PPostHandler -->
    <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
    
    <input type="hidden" name="<?=$callbackId ?>"  value="1"/>
    <input type="hidden" name="IdMemberToVerify"  value="<?=$m->id ?>"/>
    
    <table border="0">
    <tr><td>
        <?=$words->getFormatted("verifymembers_name_to_check", $m->FirstName, "<i>".$m->SecondName."</i>", $m->LastName) ?>
    </td></tr>
    <tr><td>
        <?=$words->getFormatted("verifymembers_address_to_check", $m->HouseNumber, $m->StreetName,$m->Zip, $m->CityName) ?>
    </td></tr>
    <tr><td>
        <input type="checkbox" name="NameConfirmed">
        <?=$words->getFormatted("verifymembers_IdoConfirmTheName", $m->Username) ?>
    </td></tr>
    <tr><td>
        <input type="checkbox" name="AddressConfirmed">
        <?=$words->getFormatted("verifymembers_IdoConfirmTheAdress", $m->Username) ?>
    </td></tr>
    <tr><td align="left">
        <?=$words->getFormatted("verifymembers_Comment") ?>
        <br>
        <textarea name="comment" cols="50" rows="5"></textarea>
    </td></tr>
    <tr><td align="center">
        <input type="submit" value="<?=$words->getFormatted("verifymembers_proceedtocheck") ?>">
    </td></tr>
    </table>
    
</form>
</p>

