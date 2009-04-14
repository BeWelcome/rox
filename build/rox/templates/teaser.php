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
$words = new MOD_words();

?>

<div id="teaser" class="clearfix">
  <div class="subcolumns">
    <div class="c62l">
      <div class="subcl">
        <h1 class="slogan"><span id="something" ><?php echo $words->get('IndexPageTeaserReal1a');?></span> <span id="real" ><?php echo $words->get('IndexPageTeaserReal1b');?></span>&nbsp;</h1>
        <h2><?php echo $words->get('IndexPageTeaserReal2');?></h2>
		<table>
		<tr>
		<td>
		  <div class="video-embedded">
			<!--<img src="images/misc/video-placeholder.png">-->
			<object width="306" height="172"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=3545292&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=3545292&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="306" height="172"></embed></object>
		  </div>
		</td>
		<td style="vertical-align: bottom">
		  <div class="video-desc">
		  	<p><?=$words->get('IndexPageVideoDesc','username') //TODO: Fix the 'username' to something dynamic ?></p>
		  </div>
		</td>
		</tr>
		</table>
      </div> <!-- subcl -->
    </div> <!-- c50l -->

    <div class="c38r">
      <div class="subcr">
      <?php
             $login_widget = $this->createWidget('LoginFormWidget');
            $login_widget->render();
	  ?>
        </div> <!-- subcr -->
      </div> <!-- c50r -->
    </div> <!-- subcolumns -->


</div>
