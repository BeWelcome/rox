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


JeanYves notes : This is what is used to manage the report to moderators

*/
$words = new MOD_words();

?>
<div class="col-12"><h3>Report to moderator</h3></div>

<?php
if (!empty($DataPost->Error)) {
    echo '<div class="col-12 alert alert-danger"><h3>'.$DataPost->Error.'</h3></div>';
}

$request = PRequest::get()->request;
$uri = implode('/', $request);

?>

<table class="table table-bordered table-sm">
    <? if (isset($DataPost->Thread->title)) ?>
    <form method="post" action="forums/modeditpost/<?=$DataPost->Post->id;?>" id="modpostforum">
        <input type="hidden" name="<?=$callbackId;?>"  value="1">
        <input type="hidden" name="IdThread" value="<?=$DataPost->Thread->id;?>">
        <input type="hidden" name="IdPost" value="<?=$DataPost->Post->id;?>">
            <tr><td>
        <?
        if (isset($DataPost->UserNameStarter)) echo "Thread started by ".$DataPost->UserNameStarter;
        ?>
        </td>
        <td>Post by <a href="members/<?= $DataPost->Post->UserNamePoster; ?>"><?= $DataPost->Post->UserNamePoster; ?></a></td>
                <td><a href="forums/s<?= $DataPost->Thread->id ;?>/#post<?= $DataPost->Post->id; ?>" class="btn btn-primary btn-sm btn-block">go to post</a></td>
        </tr>
        <tr><td colspan="3" class="h5"><?= $DataPost->Thread->Title[0]->Sentence; ?></td></tr>
        <tr><td colspan="3" ><?= $DataPost->Post->Content[0]->Sentence; ?></td></tr>
        <tr><td colspan="3"><label for="PostComment">Your message to the moderators:</label><textarea name="PostComment" class="w-100" rows="2"></textarea></td></tr>

        <tr>
            <td colspan="3"><label for="Status">Status</label>
                <select Name="Status">
        <?
        $Status = "Open";
        if (isset($DataPost->Report->Status)) $Status=$DataPost->Report->Status;
        ?>
        <option value="Open"<? if ($Status=='Open') echo ' selected'; ?>>Open</option>
        <option value="OnDiscussion"<? if ($Status=='OnDiscussion') echo ' selected'; ?>>In discussion</option>
        <option value="Closed"<? if ($Status=='Closed') echo ' selected';?>>Closed</option>
        </select>
            </td>
        </tr>

        <tr>
            <td colspan="3">
                <?
                $IdReporter=0 ;
                if (isset($DataPost->Report->IdReporter)) $IdReporter=$DataPost->Report->IdReporter ;
                echo "<input type='hidden' name='IdReporter' value='".$IdReporter."'>"; ?>

                <input type="submit" name="submit" value="Add to report" class="btn btn-primary">
            </td>
        </tr>

<?
if (isset($DataPost->Report->PostComment))  {
    echo '<tr><td colspan="3">'.$DataPost->Report->PostComment.'</td></tr>';
}
?>
</form></table>

