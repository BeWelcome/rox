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
 * @author Felix van Hove <fvanhove@gmx.de>
 */

$words = new MOD_words();

?>
<h3><?php echo $words->getBuffered("WordsDownload_Title"); ?></h3>
<p><?php echo $words->getFormatted('WordsDownload_Description'); ?></p>
<p>
<form id="wordsdownload" name="wordsdownload" method="post" action="admin/wordsdownload" />
<input type="checkbox" name="Replace">&nbsp;&nbsp;<?php echo $words->getBuffered("WordsDownload_ReplaceOption"); ?><br /><br />
<input type="hidden" name="<?php echo $callbackId; ?>" value="1" />
<input type="submit" class="button" name="SubmitDownload" value="<?php echo $words->getBuffered("WordsDownload_SubmitButton"); ?>" />
</form>
<?php if($_SERVER["HTTP_HOST"] == "localhost") { ?>
<p>
<br />
<h3><?php echo $words->getBuffered("WordsUpload_Title"); ?></h3>
<p><?php echo $words->getFormatted('WordsUpload_Description'); ?></p>
<form id="wordsdownload" name="wordsdownload" method="post" action="admin/wordsdownload" enctype="multipart/form-data" />
<input type="hidden" name="<?php echo $callbackId; ?>" value="1" />
<input type="hidden" name="MAX_FILE_SIZE" value="1" />
<input name="importfile" type="file" onchange="getElementById('importfilename').value=this.value.replace(/\\/g, '\\\\\\');" /><br />
<input name="importfilename" id="importfilename" type="hidden" />
<input type="submit" class="button" name="SubmitUpload" value="<?php echo $words->getBuffered("WordsUpload_SubmitButton"); ?>" />
</form>
<?php } ?>
